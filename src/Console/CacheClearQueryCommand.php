<?php namespace Mitch\LaravelDoctrine\Console;

use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CacheClearQueryCommand extends Command {

    protected $name = 'doctrine:cache:clear-query';
    protected $description = 'Clear all query cache of the various cache drivers.';

    public function fire() {
        $entityManager = $this->laravel->make('Doctrine\ORM\EntityManagerInterface');
        $cacheDriver = $entityManager->getConfiguration()->getQueryCacheImpl();
        if ( ! $cacheDriver)
            throw new InvalidArgumentException('No Query cache driver is configured on given EntityManager.');
        if ($cacheDriver instanceof ApcCache)
            throw new LogicException("Cannot clear APC Cache from Console, its shared in the Webserver memory and not accessible from the CLI.");
        if ($cacheDriver instanceof XcacheCache)
            throw new LogicException("Cannot clear XCache Cache from Console, its shared in the Webserver memory and not accessible from the CLI.");

        $this->info('Clearing ALL Query cache entries...');
        $result = $cacheDriver->deleteAll();
        $message = ($result) ? 'Successfully deleted cache entries.' : 'No cache entries were deleted.';
        if ($this->option('flush')) {
            $result = $cacheDriver->flushAll();
            $message = ($result) ? 'Successfully flushed cache entries.' : $message;
        }
        $this->info($message);
    }

    protected function getOptions() {
        return [
            ['flush', null, InputOption::VALUE_NONE, 'Dump generated SQL instead of executing query.'],
        ];
    }
} 
