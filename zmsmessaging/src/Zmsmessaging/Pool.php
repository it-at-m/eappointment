<?php

class PooledWorker extends Worker {
    public function run(){}
}

class Pool {
    protected $size;
    protected $workers = [];

    /**
     * Construct a worker pool of the given size
     * @param integer $size
     */
    public function __construct($size) {
        $this->size = $size;
    }

    /**
     * Start worker threads
     */
    public function start() {
        for ($i = 0; $i < $this->size; $i++) {
            $worker = new PooledWorker();
            $worker->start();
            $this->workers[] = $worker;
        }
        return count($this->workers);
    }

    /**
     * Submit a task to pool
     */
    public function submit(Stackable $task) {
        $this->workers[array_rand($this->workers)]->stack($task);
        return $task;
    }

    /**
     * Shutdown worker threads
     */
    public function shutdown() {
        foreach ($this->workers as $worker) {
            $worker->shutdown();
        }
    }
}
