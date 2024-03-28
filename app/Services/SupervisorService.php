<?php

namespace App\Services;

class SupervisorService
{
    protected \fXmlRpc\Client $client;
    public \Supervisor\Process $process;
    public \Supervisor\Supervisor $supervisor;

    const RESTART_DELAY = 2;

    /**
     * SupervisorService constructor.
     */
    public function __construct()
    {
        if (config('system.supervisor.enabled')) {
            $this->initialize();
        }
    }

    /**
     * Get the supervisor's xmlRPC endpoint.
     */
    public function getSupervisorUrl(): string
    {
        return 'http://'.config('system.supervisor.host').':'.config('system.supervisor.port').'/RPC2';
    }

    /**
     * Initialize the supervisor client.
     *
     * @throws \Throwable
     */
    final public function initialize(): void
    {
        throw_if(! config('system.supervisor.enabled'), new \Exception('Supervisor Monitoring is not enabled.'));

        $this->client = new \fXmlRpc\Client(
            $this->getSupervisorUrl(),
            new \fXmlRpc\Transport\HttpAdapterTransport(
                new \Http\Message\MessageFactory\GuzzleMessageFactory(),
                new \Http\Adapter\Guzzle7\Client(new \GuzzleHttp\Client())
            )
        );

        $this->supervisor = new \Supervisor\Supervisor($this->client);

        $this->setProcess(config('system.supervisor.process_name'));
    }

    /**
     * @return void
     */
    final public function setProcess(string $process)
    {
        $this->process = $this->supervisor->getProcess($process);
    }

    /**
     * Returns array of process info.
     */
    public function info(): array
    {
        return $this->supervisor->getProcessInfo(config('system.supervisor.process_name'));
    }

    /**
     * Checks if the supervisor is running.
     */
    public function isRunning(): bool
    {
        return $this->process->isRunning();
    }

    /**
     * Starts the supervisor.
     *
     * @return void
     */
    public function start()
    {
        $this->supervisor->startProcess(name: config('system.supervisor.process_name'), wait: true);
    }

    /**
     * Stops the supervisor.
     *
     * @return void
     */
    public function stop()
    {
        $this->supervisor->stopProcess(config('system.supervisor.process_name'));
    }

    /**
     * Restarts the supervisor.
     *
     * @return void
     */
    public function restart()
    {
        if ($this->isRunning()) {
            $this->stop();

            sleep(self::RESTART_DELAY);
        }

        $this->start();
    }
}
