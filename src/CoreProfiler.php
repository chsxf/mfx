<?php

namespace chsxf\MFX;

use chsxf\MFX\Services\IProfilingService;
use Twig\Environment;

/**
 * Core profiler class
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 1.0
 */
final class CoreProfiler implements IProfilingService
{
    /**
     * @var boolean Flag indicating if the class should fill the profiling data
     */
    private bool $ticking;
    /**
     * @var float Profiling start time as returned by microtime(true);
     */
    private float $profilingStartTime;
    /**
     * @var float Profiling end time as returned by microtime(true);
     */
    private float $profilingEndTime;
    /**
     * @var array Profiling data
     */
    private array $profilingData;
    /**
     * @var int Last annotation index
     */
    private int $lastAnnotation = 0;

    /**
     * Constructor
     * @since 2.0
     */
    public function __construct(private readonly bool $active)
    {
        $this->profilingStartTime = microtime(true);
        $this->profilingEndTime = 0;
        $this->profilingData = array();
        $this->ticking = $active;

        if ($active) {
            register_tick_function($this->tickHandler(...));
            ob_start();

            declare(ticks=1);
        }
    }

    /**
     * Tells if the profiler is active or not
     * @since 2.0
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Tick handler used for gathering profiling data
     *
     * @since 2.0
     * @ignore
     * @param string $event Custom event annotation to identify event times during profiling. If NULL, no event is provided (Defaults to NULL).
     */
    private function tickHandler(?string $event = null)
    {
        if ($this->ticking || !empty($event)) {
            $tickData = array(
                microtime(true) - $this->profilingStartTime,
                memory_get_usage(),
                memory_get_usage(true)
            );
            if (!empty($event)) {
                $tickData[] = ++$this->lastAnnotation;
                $tickData[] = $event;
            }
            $this->profilingData[] = $tickData;
        }
    }

    /**
     * Push a custom event into profiling data
     * @since 2.0
     * @param string $event Name of the even
     */
    public function pushEvent(string $event): void
    {
        if ($this->active && empty($this->profilingEndTime)) {
            $this->tickHandler($event);
        }
    }

    /**
     * Terminates profiling and output buffering and echoes the result
     * @since 2.0
     * @ignore
     */
    public function stop(Environment $twig)
    {
        if (!$this->active) {
            return;
        }

        unregister_tick_function($this->tickHandler(...));
        $this->profilingEndTime = microtime(true);

        $peak = memory_get_usage();
        $realPeak = memory_get_peak_usage(true);

        $ini_output_buffering = ini_get('output_buffering');
        $minLevel = empty($ini_output_buffering) ? 1 : 2;
        while (ob_get_level() > $minLevel) {
            ob_end_flush();
        }
        $buffer = ob_get_contents();
        ob_clean();

        $memlimit = ini_get('memory_limit');
        $regs = null;
        preg_match('/^([1-9]\d*)([kmg])?$/i', $memlimit, $regs);
        if (!empty($regs[2])) {
            switch ($regs[2]) {
                case 'k':
                case 'K':
                    $memlimit = $regs[1] * 1024;
                    break;
                case 'm':
                case 'M':
                    $memlimit = $regs[1] * pow(1024, 2);
                    break;
                case 'g':
                case 'G':
                    $memlimit = $regs[1] * pow(1024, 3);
                    break;
            }
        }

        $headers = headers_list();
        $contentType = null;
        foreach ($headers as $header) {
            if (preg_match('/^Content-Type: ([^;]+)/', $header, $contentType)) {
                $contentType = $contentType[1];
                break;
            }
        }

        $preparedProfilingData = $this->profilingData;
        if ($contentType != 'application/json' && $contentType != 'application/xml') {
            $preparedProfilingData = array_map(function ($item) use ($contentType) {
                if (count($item) < 5) {
                    $item = array_pad($item, 5, 'null');
                } else {
                    if ($contentType == 'text/html') {
                        $item[3] = sprintf('"%d"', $item[3]);
                    }
                    $item[4] = sprintf('"%s"', $item[4]);
                }
                return $item;
            }, $preparedProfilingData);
        }

        $context = array(
            'duration' => $this->getProfilingDuration(),
            'opCount' => count($this->profilingData),
            'memPeakUsage' => $peak,
            'memPeakUsageRatio' => $peak / $memlimit,
            'memRealPeakUsage' => $realPeak,
            'memRealPeakUsageRatio' => $realPeak / $memlimit,
            'data' => $preparedProfilingData
        );

        // HTML
        if ($contentType == 'text/html') {
            $str = $twig->render('@mfx/Profiler_HTML.twig', $context);
            echo preg_replace('/<!--\s+--MFX_PROFILING_OUTPUT--\s+-->/', $str, $buffer);
        }
        // JSON
        elseif ($contentType == 'application/json') {
            $decoded = json_decode($buffer);
            if (is_object($decoded)) {
                $decoded->mfx_profiler = $context;
                echo json_encode($decoded);
            } else {
                echo $buffer;
            }
        }
        // XML
        elseif ($contentType == 'application/xml') {
            $xmlTree = simplexml_load_string($buffer);

            $profilerRoot = $xmlTree->addChild('mfx_profiler');
            foreach ($context as $k => $v) {
                if ($k == 'data') {
                    $dataRoot = $profilerRoot->addChild($k);
                    foreach ($context['data'] as $row) {
                        $dataRow = $dataRoot->addChild('row');
                        $dataRow->addAttribute('timing', $row[0]);
                        $dataRow->addAttribute('memory_usage', $row[1]);
                        $dataRow->addAttribute('memore_real_usage', $row[2]);
                        if (count($row) > 3) {
                            $dataRow->addAttribute('annotation_index', $row[3]);
                            $dataRow->addChild('event', $row[4]);
                        }
                    }
                } else {
                    $profilerRoot->addChild($k, $v);
                }
            }

            echo $xmlTree->asXML();
        }
        // Text plain
        elseif ($contentType == 'text/plain') {
            $str = $twig->render('@mfx/Profiler_Plain.twig', $context);
            echo "{$buffer}\n\n{$str}";
        }
        // Unsupported content-type
        else {
            echo $buffer;
        }
    }

    /**
     * Evaluates how long was the profiling
     * @since 2.0
     * @return boolean|float false if profiling is not initialized or complete, or the duration in milliseconds
     */
    public function getProfilingDuration(): float|false
    {
        if (!$this->active || empty($this->profilingEndTime)) {
            return false;
        }
        return ($this->profilingEndTime - $this->profilingStartTime);
    }
}
