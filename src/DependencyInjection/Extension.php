<?php
declare(strict_types=1);

namespace ControlBit\ResetCode\DependencyInjection;

use ControlBit\ResetCode\Doctrine\Schema;
use ControlBit\ResetCode\Service\ResetCodeManager;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension as SymfonyExtension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @psalm-type TablesConfig = array<string, array{
 *     name: string,
 *     alias: string|null,
 *     connection_name: string,
 *     code_size: int,
 *     ttl: int,
 *     timeout_to_clear_oldest_ms: int,
 *     allow_subject_duplicates: bool
 * }>
 *
 * @psalm-type Config = array{
 *     enabled: bool,
 *     tables: TablesConfig
 * }
 */
final class Extension extends SymfonyExtension
{
    public function getAlias(): string
    {
        return 'reset_code';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();

        /** @var Config $config */
        $config = $this->processConfiguration($configuration, $configs);

        if (false === $config['enabled']) {
            return;
        }

        if (empty($config['tables'])) {
            throw new InvalidConfigurationException('Tables, configuration value, must contain at least one item');
        }

        $this->registerServices($config['tables'], $container);

        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/Config')
        );

        $loader->load('commands.xml');
    }

    /**
     * @param  TablesConfig             $tablesConfig
     */
    private function registerServices(array $tablesConfig, ContainerBuilder $container): void
    {
        foreach ($tablesConfig as $tableConfig) {
            $name                   = $this->prepareName($tableConfig['name']);
            $tableName              = Schema::TABLE_PREFIX.'_'.$this->prepareName($tableConfig['name']);
            $alias                  = null === $tableConfig['alias'] ? null : $this->prepareName($tableConfig['alias']);
            $codeSize               = (int)$tableConfig['code_size'];
            $ttl                    = (int)$tableConfig['ttl'];
            $timeoutToClearOldestMs = (int)$tableConfig['timeout_to_clear_oldest_ms'];
            $allowSubjectDuplicates = (bool)$tableConfig['allow_subject_duplicates'];
            $connectionName         = $tableConfig['connection_name'];

            $connectionReference = new Reference(
                \sprintf('doctrine.dbal.%s_connection', $tableConfig['connection_name'])
            );

            $this->registerSchemaEvents($container, $connectionName, $codeSize, $tableName);
            $this->registerResetCodeManagers(
                $container,
                $connectionReference,
                $codeSize,
                $ttl,
                $timeoutToClearOldestMs,
                $allowSubjectDuplicates,
                $name,
                $tableName,
                $alias
            );
        }
    }

    private function registerSchemaEvents(
        ContainerBuilder $container,
        string           $connectionName,
        int              $codeSize,
        string           $tableName,
    ): void {
        $definition = new Definition(Schema::class, [$codeSize, $tableName]);

        $definition->addTag('doctrine.event_listener', [
            'event'      => 'postGenerateSchema',
            'connection' => $connectionName,
        ]);
        $container->setDefinition(\sprintf("reset_code.post_generate_schema.%s", $tableName), $definition);
    }

    private function registerResetCodeManagers(
        ContainerBuilder $container,
        Reference        $connectionReference,
        int              $codeSize,
        int              $ttl,
        int              $timeoutToClearOldestMs,
        bool             $allowSubjectDuplicates,
        string           $name,
        string           $tableName,
        ?string          $alias,
    ): void {
        $definition = new Definition(ResetCodeManager::class, [
            $connectionReference,
            $tableName,
            $codeSize,
            $ttl,
            $timeoutToClearOldestMs,
            $allowSubjectDuplicates,
        ]);

        $definition->setPublic(true);
        $definition->addTag('reset_code.manager');

        $id = \sprintf("reset_code.%s", $name);
        $container->setDefinition($id, $definition);

        if (null !== $alias) {
            $container->setDefinition(\sprintf("reset_code.%s", $alias), $definition);
        }

        if (!$container->has('reset_code.default')) {
            $container->setDefinition('reset_code.default', $definition);
            $container->setDefinition(ResetCodeManager::class, $definition);
        }
    }

    private function prepareName(string $str): string
    {
        $str = \trim($str);
        $str = \preg_replace('/[\s\d]/', '_', $str);

        return \strtolower((string)$str);
    }
}