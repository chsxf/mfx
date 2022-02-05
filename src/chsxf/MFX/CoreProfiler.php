<?php
/**
 * Profiling tool
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

/**
 * Core profiler class
 */
class CoreProfiler
{
	/**
	 * @var CoreProfiler Singleton instance
	 */
	private static ?CoreProfiler $_singleInstance = NULL;
	
	/**
	 * @var boolean Flag indicating if the class should fill the profiling data
	 */
	private bool $_ticking = true;
	/**
	 * @var float Profiling start time as returned by microtime(true);
	 */
	private float $_profilingStartTime;
	/**
	 * @var float Profiling end time as returned by microtime(true);
	 */
	private float $_profilingEndTime;
	/**
	 * @var array Profiling data
	 */
	private array $_profilingData;
	/**
	 * @var int Last annotation index
	 */
	private int $_lastAnnotation = 0;
	
	/**
	 * Constructor
	 */
	private function __construct() {
		$this->_profilingStartTime = microtime(true);
		$this->_profilingEndTime = 0;
		$this->_profilingData = array();
	}
	
	/**
	 * Tick handler used for gathering profiling data
	 * @param string $event Custom event annotation to identify event times during profiling. If NULL, no event is provided (Defaults to NULL).
	 */
	public function tickHandler(?string $event = NULL) {
        if ($this->_ticking || !empty($event)) {
            $this->_profilingData[] = array(
                    microtime(true) - $this->_profilingStartTime,
                    memory_get_usage(),
                    memory_get_usage(true),
                    empty($event) ? 'null' : sprintf('%d', ++$this->_lastAnnotation),
                    empty($event) ? 'null' : $event
            );
        }
	}
	
	/**
	 * Initiliases profiling
	 * 
	 * This function enables output buffering.
	 */
	public static function init() {
        if (self::$_singleInstance !== null) {
            return;
        }
		
		self::$_singleInstance = new CoreProfiler();
		register_tick_function(array(&self::$_singleInstance, 'tickHandler'));
		Scripts::add('https://www.google.com/jsapi');
		ob_start();
		
		declare(ticks = 1);
	}
	
	/**
	 * Push a custom event into profiling data
	 * @param string $event Name of the even
	 */
	public static function pushEvent(string $event) {
        if (self::$_singleInstance === null || !empty(self::$_singleInstance->_profilingEndTime)) {
            return;
        }
		
		self::$_singleInstance->_ticking = false;
        if (self::$_singleInstance !== null) {
            self::$_singleInstance->tickHandler($event, true);
        }
		self::$_singleInstance->_ticking = true;
	}
	
	/**
	 * Terminates profiling and output buffering and echoes the result
	 */
	public static function stop() {
        if (self::$_singleInstance === null) {
            return;
        }
		
		unregister_tick_function(array(&self::$_singleInstance, 'tickHandler'));
		self::$_singleInstance->_profilingEndTime = microtime(true);
		
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
		$regs = NULL;
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
		$contentType = NULL;
		$charset = NULL;
		foreach ($headers as $header) {
			if (preg_match('/^Content-Type: ([^;]+)/', $header, $contentType)) {
				$contentType = $contentType[1];
				break;
			}
		}
		
		$context = array(
				'duration' => self::getProfilingDuration(),
				'opCount' => count(self::$_singleInstance->_profilingData),
				'memPeakUsage' => $peak,
				'memPeakUsageRatio' => $peak / $memlimit,
				'memRealPeakUsage' => $realPeak,
				'memRealPeakUsageRatio' => $realPeak / $memlimit,
				'data' => self::$_singleInstance->_profilingData
		);
		
		// HTML
		if ($contentType == 'text/html') {
			$str = $GLOBALS['twig']->render('@mfx/Profiler_HTML.twig', $context);
			echo preg_replace('/<!--\s+--MFX_PROFILING_OUTPUT--\s+-->/', $str, $buffer);
		}
		// JSON
		else if ($contentType == 'application/json') {
			$decoded = json_decode($buffer);
			if (is_object($decoded)) {
				$decoded->mfx_profiler = $context;
				echo json_encode($decoded);
			}
			else {
                echo $buffer;
            }
		}
		// XML
		else if ($contentType == 'application/xml') {
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
				}
				else {
                    $profilerRoot->addChild($k, $v);
                }
			}
			
			echo $xmlTree->asXML();
		}
		// Text plain
		else if ($contentType == 'text/plain') {
			$str = $GLOBALS['twig']->render('@mfx/Profiler_Plain.twig', $context);
			echo "{$buffer}\n\n{$str}";
		}
		// Unsupported content-type
		else {
            echo $buffer;
        }
	}
	
	/**
	 * Evaluates how long was the profiling
	 * @return boolean|float false if profiling is not initialized or complete, or the duration in milliseconds
	 */
	public static function getProfilingDuration(): float|false {
        if (self::$_singleInstance === null || empty(self::$_singleInstance->_profilingEndTime)) {
            return false;
        }
		return (self::$_singleInstance->_profilingEndTime - self::$_singleInstance->_profilingStartTime);
	}
}