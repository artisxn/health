<?php

namespace codicastudio\Health\Tests\PhpUnit\Service;

use Illuminate\Support\Str;
use codicastudio\Health\Commands;
use codicastudio\Yaml\Package\Yaml;
use Illuminate\Support\Collection;
use codicastudio\Health\Support\ResourceLoader;
use codicastudio\Health\Tests\PhpUnit\TestCase;
use codicastudio\Health\Http\Controllers\Health as HealthController;

class ServiceTest extends TestCase
{
    private $service;

    private $resources;

    private function getResources($force = false)
    {
        if ($force || ! $this->resources) {
            $this->resources = $this->service->checkResources($force);
        }

        return $this->resources;
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $this->app = $app;

        $this->app['config']->set(
            'health.resources_location.path',
            package_resources_dir()
        );
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->service = app('codicastudio.health');
    }

    private function sortChars($string)
    {
        $stringParts = str_split($string);

        sort($stringParts);

        return implode('', $stringParts);
    }

    public function testResourcesWhereChecked()
    {
        $this->assertCheckedResources($this->getResources());
    }

    public function testCacheFlush()
    {
        $this->assertCheckedResources($this->getResources(true));
    }

    public function testUnsorted()
    {
        $this->app['config']->set('health.sort_by', null);

        $this->assertCheckedResources($this->getResources(true));
    }

    public function testInvalidEnabledResources()
    {
        $this->expectException(\DomainException::class);

        $this->app['config']->set('health.resources.enabled', 'invalid');

        (new ResourceLoader(new Yaml()))->load();

        $this->getResources(true);
    }

    public function testInvalidLoadOneResource()
    {
        $this->app['config']->set('health.resources.enabled', ['Database']);

        $resource = (new ResourceLoader(new Yaml()))->load();

        $this->assertTrue($resource->first()['name'] == 'Database');
    }

    public function assertCheckedResources($resources)
    {
        $healthCount = $resources->reduce(function ($carry, $resource) {
            return $carry + ($resource->isHealthy() ? 1 : 0);
        }, 0);

        $this->assertGreaterThanOrEqual(
            static::RESOURCES_HEALTHY,
            $healthCount
        );

        $failing = $resources->filter(function ($resource) {
            return $resource->isHealthy();
        });

        $this->assertGreaterThanOrEqual(
            static::RESOURCES_HEALTHY_EVERYWHERE,
            $failing->count()
        );
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf(Collection::class, $this->getResources());
    }

    public function testResourcesHasTheCorrectCount()
    {
        $this->assertCount(
            count(static::ALL_RESOURCES),
            $this->getResources()->toArray()
        );
    }

    public function testResourcesItemsMatchConfig()
    {
        $this->assertEquals(
            collect(static::ALL_RESOURCES)
                ->map(function ($value) {
                    return strtolower($value);
                })
                ->sort()
                ->values()
                ->toArray(),
            $this->getResources()
                ->keys()
                ->map(function ($value) {
                    return strtolower($value);
                })
                ->sort()
                ->values()
                ->toArray()
        );
    }

    public function testArtisanCommands()
    {
        $commands = ['panel', 'check'];

        foreach ($commands as $command) {
            (new Commands($this->service))->$command();
        }

        $this->assertFalse(! true);
    }

    public function testController()
    {
        $controller = new HealthController($this->service);

        $this->assertEquals(
            collect(
                json_decode($controller->check()->getContent(), true)
            )->count(),
            count(static::ALL_RESOURCES)
        );

        $this->assertTrue(
            Str::startsWith($controller->panel()->getContent(), '<!DOCTYPE html>')
        );

        $this->assertTrue(count($controller->config()) > 10);

        $this->assertTrue(
            $controller->getResource('app-key')->name == 'App Key'
        );

        $this->assertTrue(
            $controller->allResources()->count() == count(static::ALL_RESOURCES)
        );
    }
}
