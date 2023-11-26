<?php
namespace Contract;

/**
 * Event listener interface
 *
 * @author Igor Shvartsev (igor.shvartsev@gmail.com)
 */
interface EventListenerInterface
{
	/**
	 * Event handler
	 *
	 * @param object $event event object
	 * @param array $payload additional data
	 */
    public function handle($event, $payload = []);
}
