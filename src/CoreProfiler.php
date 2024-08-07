<?php

/**
 * Profiling tool
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

/**
 * Core profiler class
 * @since 1.0
 */
final class CoreProfiler
{
    /**
     * @var CoreProfiler Singleton instance
     */
    private static ?CoreProfiler $singleInstance = null;

    /**
     * @var boolean Flag indicating if the class should fill the profiling data
     */
    private bool $ticking = true;
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
     */
    private function __construct()
    {
        $this->profilingStartTime = microtime(true);
        $this->profilingEndTime = 0;
        $this->profilingData = array();
    }

    /**
     * Tick handler used for gathering profiling data
     *
     * @ignore
     *
     * @param string $event Custom event annotation to identify event times during profiling. If NULL, no event is provided (Defaults to NULL).
     */
    public function tickHandler(?string $event = null)
    {
        if ($this->ticking || !empty($event)) {
            $this->profilingData[] = array(
                microtime(true) - $this->profilingStartTime,
                memory_get_usage(),
                memory_get_usage(true),
                empty($event) ? 'null' : sprintf('"%d"', ++$this->lastAnnotation),
                empty($event) ? 'null' : "\"$event\""
            );
        }
    }

    /**
     * Initiliases profiling
     *
     * This function enables output buffering.
     *
     * @since 1.0
     */
    public static function init()
    {
        if (self::$singleInstance !== null) {
            return;
        }

        self::$singleInstance = new CoreProfiler();
        register_tick_function(array(&self::$singleInstance, 'tickHandler'));
        Scripts::add('https://www.google.com/jsapi');
        ob_start();

        declare(ticks=1);
    }

    /**
     * Push a custom event into profiling data
     * @since 1.0
     * @param string $event Name of the even
     */
    public static function pushEvent(string $event)
    {
        if (self::$singleInstance === null || !empty(self::$singleInstance->profilingEndTime)) {
            return;
        }

        self::$singleInstance->ticking = false;
        if (self::$singleInstance !== null) {
            self::$singleInstance->tickHandler($event);
        }
        self::$singleInstance->ticking = true;
    }

    /**
     * Terminates profiling and output buffering and echoes the result
     * @since 1.0
     */
    public static function stop()
    {
        if (self::$singleInstance === null) {
            return;
        }

        unregister_tick_function(array(&self::$singleInstance, 'tickHandler'));
        self::$singleInstance->profilingEndTime = microtime(true);

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

        $context = array(
            'duration' => self::getProfilingDuration(),
            'opCount' => count(self::$singleInstance->profilingData),
            'memPeakUsage' => $peak,
            'memPeakUsageRatio' => $peak / $memlimit,
            'memRealPeakUsage' => $realPeak,
            'memRealPeakUsageRatio' => $realPeak / $memlimit,
            'data' => self::$singleInstance->profilingData
        );

        // HTML
        if ($contentType == 'text/html') {
            $str = CoreManager::getTwig()->render('@mfx/Profiler_HTML.twig', $context);
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
                        $dataRow->addAttribute('annotation_index', $row[3]);
                        $dataRow->addChild('event', $row[4]);
                    }
                } else {
                    $profilerRoot->addChild($k, $v);
                }
            }

            echo $xmlTree->asXML();
        }
        // Text plain
        elseif ($contentType == 'text/plain') {
            $str = CoreManager::getTwig()->render('@mfx/Profiler_Plain.twig', $context);
            echo "{$buffer}\n\n{$str}";
        }
        // Unsupported content-type
        else {
            echo $buffer;
        }
    }

    /**
     * Evaluates how long was the profiling
     * @since 1.0
     * @return boolean|float false if profiling is not initialized or complete, or the duration in milliseconds
     */
    public static function getProfilingDuration(): float|false
    {
        if (self::$singleInstance === null || empty(self::$singleInstance->profilingEndTime)) {
            return false;
        }
        return (self::$singleInstance->profilingEndTime - self::$singleInstance->profilingStartTime);
    }
}
