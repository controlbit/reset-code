<?php
declare(strict_types=1);

namespace Choks\ResetCode\Tests\Resources\App;

use Choks\DoctrineUtils\DoctrineUtils;
use Choks\ResetCode\ResetCode;
use Choks\ResetCode\Tests\Resources\App\Fixtures\AppFixtures;
use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

class ResetCodeTestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new DoctrineFixturesBundle(),
            new DAMADoctrineTestBundle(),
            new DoctrineUtils(),
            new ResetCode(),
        ];
    }

    /**
     * @phpstan-ignore-next-line
     */
    private function configureContainer(
        ContainerConfigurator $container,
        LoaderInterface       $loader,
        ContainerBuilder      $builder,
    ): void {

        $container->extension(
            'reset_code',
            [
                'tables' => [
                    [
                        'name' => 'alpha',
                        'ttl'  => 1,
                    ],
                    [
                        'name'  => 'beta',
                        'alias' => 'two',
                    ],
                    [
                        'name'                     => 'ddos',
                        'code_size'                => 1,
                        'allow_subject_duplicates' => true,
                    ],
                ],
            ]
        );

        $container->extension(
            'framework',
            [
                'test'                  => true,
                'http_method_override'  => false,
                'handle_all_throwables' => true,
                'php_errors'            => [
                    'log' => true,
                ],
                'validation'            => [
                    'email_validation_mode' => 'html5',
                ],
                'uid'                   => [
                    'time_based_uuid_version' => 7,
                    'default_uuid_version'    => 7,
                ],
            ]
        );

        $container->extension('doctrine', [
            'dbal' => [
                'driver'         => 'pdo_mysql',
                'url'            => 'mysql://db:db@monorepo-libs-mysql/reset_code_database',
                'use_savepoints' => true,
            ],
            'orm'  => [
                'report_fields_where_declared' => true,
                'auto_generate_proxy_classes'  => true,
                'naming_strategy'              => 'doctrine.orm.naming_strategy.underscore_number_aware',
                'auto_mapping'                 => true,
                'enable_lazy_ghost_objects'    => true,
                'mappings'                     => [
                    'Tests' => [
                        'is_bundle' => false,
                        'type'      => 'attribute',
                        'dir'       => __DIR__.'/Entity',
                        'prefix'    => 'Choks\ResetCode\Tests\Resources\App',
                    ],
                ],
            ],
        ]);

        $container->services()->set('logger', NullLogger::class);
        $this->configureServices($container);
    }

    private function configureServices(ContainerConfigurator $container): void
    {
        $container
            ->services()
            ->set(AppFixtures::class)
            ->tag('doctrine.fixture.orm')
        ;
    }
}