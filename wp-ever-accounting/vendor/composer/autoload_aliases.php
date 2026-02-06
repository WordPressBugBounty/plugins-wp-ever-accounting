<?php

// Functions and constants

namespace {

}


namespace EverAccounting {

    class AliasAutoloader
    {
        private string $includeFilePath;

        private array $autoloadAliases = array (
  'ByteKit\\Models\\Model' => 
  array (
    'type' => 'class',
    'classname' => 'Model',
    'isabstract' => true,
    'namespace' => 'ByteKit\\Models',
    'extends' => 'EverAccounting\\ByteKit\\Models\\Model',
    'implements' => 
    array (
    ),
  ),
  'ByteKit\\Models\\Post' => 
  array (
    'type' => 'class',
    'classname' => 'Post',
    'isabstract' => true,
    'namespace' => 'ByteKit\\Models',
    'extends' => 'EverAccounting\\ByteKit\\Models\\Post',
    'implements' => 
    array (
    ),
  ),
  'ByteKit\\Models\\Query' => 
  array (
    'type' => 'class',
    'classname' => 'Query',
    'isabstract' => false,
    'namespace' => 'ByteKit\\Models',
    'extends' => 'EverAccounting\\ByteKit\\Models\\Query',
    'implements' => 
    array (
    ),
  ),
  'ByteKit\\Models\\Relations\\BelongsTo' => 
  array (
    'type' => 'class',
    'classname' => 'BelongsTo',
    'isabstract' => false,
    'namespace' => 'ByteKit\\Models\\Relations',
    'extends' => 'EverAccounting\\ByteKit\\Models\\Relations\\BelongsTo',
    'implements' => 
    array (
    ),
  ),
  'ByteKit\\Models\\Relations\\BelongsToMany' => 
  array (
    'type' => 'class',
    'classname' => 'BelongsToMany',
    'isabstract' => false,
    'namespace' => 'ByteKit\\Models\\Relations',
    'extends' => 'EverAccounting\\ByteKit\\Models\\Relations\\BelongsToMany',
    'implements' => 
    array (
    ),
  ),
  'ByteKit\\Models\\Relations\\HasMany' => 
  array (
    'type' => 'class',
    'classname' => 'HasMany',
    'isabstract' => false,
    'namespace' => 'ByteKit\\Models\\Relations',
    'extends' => 'EverAccounting\\ByteKit\\Models\\Relations\\HasMany',
    'implements' => 
    array (
    ),
  ),
  'ByteKit\\Models\\Relations\\HasOne' => 
  array (
    'type' => 'class',
    'classname' => 'HasOne',
    'isabstract' => false,
    'namespace' => 'ByteKit\\Models\\Relations',
    'extends' => 'EverAccounting\\ByteKit\\Models\\Relations\\HasOne',
    'implements' => 
    array (
    ),
  ),
  'ByteKit\\Models\\Relations\\Relation' => 
  array (
    'type' => 'class',
    'classname' => 'Relation',
    'isabstract' => true,
    'namespace' => 'ByteKit\\Models\\Relations',
    'extends' => 'EverAccounting\\ByteKit\\Models\\Relations\\Relation',
    'implements' => 
    array (
    ),
  ),
  'ByteKit\\Admin\\Flash' => 
  array (
    'type' => 'class',
    'classname' => 'Flash',
    'isabstract' => false,
    'namespace' => 'ByteKit\\Admin',
    'extends' => 'EverAccounting\\ByteKit\\Admin\\Flash',
    'implements' => 
    array (
    ),
  ),
  'ByteKit\\Admin\\Notices' => 
  array (
    'type' => 'class',
    'classname' => 'Notices',
    'isabstract' => false,
    'namespace' => 'ByteKit\\Admin',
    'extends' => 'EverAccounting\\ByteKit\\Admin\\Notices',
    'implements' => 
    array (
    ),
  ),
  'ByteKit\\Plugin' => 
  array (
    'type' => 'class',
    'classname' => 'Plugin',
    'isabstract' => true,
    'namespace' => 'ByteKit',
    'extends' => 'EverAccounting\\ByteKit\\Plugin',
    'implements' => 
    array (
      0 => 'ByteKit\\Interfaces\\Pluginable',
    ),
  ),
  'ByteKit\\Scripts' => 
  array (
    'type' => 'class',
    'classname' => 'Scripts',
    'isabstract' => false,
    'namespace' => 'ByteKit',
    'extends' => 'EverAccounting\\ByteKit\\Scripts',
    'implements' => 
    array (
      0 => 'ByteKit\\Interfaces\\Scriptable',
    ),
  ),
  'ByteKit\\Services' => 
  array (
    'type' => 'class',
    'classname' => 'Services',
    'isabstract' => false,
    'namespace' => 'ByteKit',
    'extends' => 'EverAccounting\\ByteKit\\Services',
    'implements' => 
    array (
      0 => 'ArrayAccess',
    ),
  ),
  'ByteKit\\Models\\Traits\\HasAttributes' => 
  array (
    'type' => 'trait',
    'traitname' => 'HasAttributes',
    'namespace' => 'ByteKit\\Models\\Traits',
    'use' => 
    array (
      0 => 'EverAccounting\\ByteKit\\Models\\Traits\\HasAttributes',
    ),
  ),
  'ByteKit\\Models\\Traits\\HasMetaData' => 
  array (
    'type' => 'trait',
    'traitname' => 'HasMetaData',
    'namespace' => 'ByteKit\\Models\\Traits',
    'use' => 
    array (
      0 => 'EverAccounting\\ByteKit\\Models\\Traits\\HasMetaData',
    ),
  ),
  'ByteKit\\Models\\Traits\\HasRelations' => 
  array (
    'type' => 'trait',
    'traitname' => 'HasRelations',
    'namespace' => 'ByteKit\\Models\\Traits',
    'use' => 
    array (
      0 => 'EverAccounting\\ByteKit\\Models\\Traits\\HasRelations',
    ),
  ),
  'ByteKit\\Traits\\HasPlugin' => 
  array (
    'type' => 'trait',
    'traitname' => 'HasPlugin',
    'namespace' => 'ByteKit\\Traits',
    'use' => 
    array (
      0 => 'EverAccounting\\ByteKit\\Traits\\HasPlugin',
    ),
  ),
  'ByteKit\\Interfaces\\Pluginable' => 
  array (
    'type' => 'interface',
    'interfacename' => 'Pluginable',
    'namespace' => 'ByteKit\\Interfaces',
    'extends' => 
    array (
      0 => 'EverAccounting\\ByteKit\\Interfaces\\Pluginable',
    ),
  ),
  'ByteKit\\Interfaces\\Scriptable' => 
  array (
    'type' => 'interface',
    'interfacename' => 'Scriptable',
    'namespace' => 'ByteKit\\Interfaces',
    'extends' => 
    array (
      0 => 'EverAccounting\\ByteKit\\Interfaces\\Scriptable',
    ),
  ),
);

        public function __construct()
        {
            $this->includeFilePath = __DIR__ . '/autoload_alias.php';
        }

        public function autoload($class)
        {
            if (!isset($this->autoloadAliases[$class])) {
                return;
            }
            switch ($this->autoloadAliases[$class]['type']) {
                case 'class':
                        $this->load(
                            $this->classTemplate(
                                $this->autoloadAliases[$class]
                            )
                        );
                    break;
                case 'interface':
                    $this->load(
                        $this->interfaceTemplate(
                            $this->autoloadAliases[$class]
                        )
                    );
                    break;
                case 'trait':
                    $this->load(
                        $this->traitTemplate(
                            $this->autoloadAliases[$class]
                        )
                    );
                    break;
                default:
                    // Never.
                    break;
            }
        }

        private function load(string $includeFile)
        {
            file_put_contents($this->includeFilePath, $includeFile);
            include $this->includeFilePath;
            file_exists($this->includeFilePath) && unlink($this->includeFilePath);
        }

        private function classTemplate(array $class): string
        {
            $abstract = $class['isabstract'] ? 'abstract ' : '';
            $classname = $class['classname'];
            if (isset($class['namespace'])) {
                $namespace = "namespace {$class['namespace']};";
                $extends = '\\' . $class['extends'];
                $implements = empty($class['implements']) ? ''
                : ' implements \\' . implode(', \\', $class['implements']);
            } else {
                $namespace = '';
                $extends = $class['extends'];
                $implements = !empty($class['implements']) ? ''
                : ' implements ' . implode(', ', $class['implements']);
            }
            return <<<EOD
                <?php
                $namespace
                $abstract class $classname extends $extends $implements {}
                EOD;
        }

        private function interfaceTemplate(array $interface): string
        {
            $interfacename = $interface['interfacename'];
            $namespace = isset($interface['namespace'])
            ? "namespace {$interface['namespace']};" : '';
            $extends = isset($interface['namespace'])
            ? '\\' . implode('\\ ,', $interface['extends'])
            : implode(', ', $interface['extends']);
            return <<<EOD
                <?php
                $namespace
                interface $interfacename extends $extends {}
                EOD;
        }
        private function traitTemplate(array $trait): string
        {
            $traitname = $trait['traitname'];
            $namespace = isset($trait['namespace'])
            ? "namespace {$trait['namespace']};" : '';
            $uses = isset($trait['namespace'])
            ? '\\' . implode(';' . PHP_EOL . '    use \\', $trait['use'])
            : implode(';' . PHP_EOL . '    use ', $trait['use']);
            return <<<EOD
                <?php
                $namespace
                trait $traitname { 
                    use $uses; 
                }
                EOD;
        }
    }

    spl_autoload_register([ new AliasAutoloader(), 'autoload' ]);
}
