<?php

/**
 * This file is part of Temporal package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Temporal\Tests\Functional\Interceptor\Client;

use Temporal\Client\WorkflowOptions;
use Temporal\Tests\Workflow\Interceptor\FooWorkflow;

/**
 * @group workflow
 * @group functional
 */
final class InterceptRequestTestCase extends InterceptorTestCase
{
    public function testSingleInterceptor(): void
    {
        $client = $this->createClient();
        $workflow = $client->newWorkflowStub(
            FooWorkflow::class,
            WorkflowOptions::new(),
        );

        $result = (array)$workflow->handler();

        // Workflow header
        $this->assertSame([
            /** @see \Temporal\Tests\Interceptor\FooHeaderIterator::execute() */
            'execute' => '1',
        ], (array)$result[0]);
        // Activity header
        $this->assertSame([
            /** @see \Temporal\Tests\Interceptor\FooHeaderIterator::execute() */
            'execute' => '1',
            /** @see \Temporal\Tests\Interceptor\FooHeaderIterator::handleOutboundRequest() */
            'handleOutboundRequest' => '1',
            /** @see \Temporal\Tests\Interceptor\FooHeaderIterator::handleActivityInbound() */
            'handleActivityInbound' => '1',
        ], (array)$result[1]);
    }
}
