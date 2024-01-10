<?php

/**
 * This file is part of Temporal package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Temporal\Internal\Transport\Router;

use React\Promise\Deferred;
use Temporal\DataConverter\EncodedValues;
use Temporal\Exception\DestructMemorizedInstanceException;
use Temporal\Internal\Repository\RepositoryInterface;
use Temporal\Internal\Workflow\Process\Process;
use Temporal\Worker\LoopInterface;
use Temporal\Worker\Transport\Command\ServerRequestInterface;

class DestroyWorkflow extends WorkflowProcessAwareRoute
{
    /**
     * @var string
     */
    private const ERROR_PROCESS_NOT_DEFINED = 'Unable to kill workflow because workflow process #%s was not found';

    public function __construct(
        RepositoryInterface $running,
        protected LoopInterface $loop
    ) {
        parent::__construct($running);
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request, array $headers, Deferred $resolver): void
    {
        $this->kill($request->getID());

        $resolver->resolve(EncodedValues::fromValues([null]));
    }

    /**
     * @param string $runId
     * @return array
     */
    public function kill(string $runId): array
    {
        /** @var Process $process */
        $process = $this->running->pull($runId);

        $process->cancel(new DestructMemorizedInstanceException());
        $this->loop->once(
            'finally',
            $process->destroy(...),
        );

        return [];
    }
}
