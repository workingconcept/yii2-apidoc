<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\markdown;

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\MethodDoc;
use yii\apidoc\models\PropertyDoc;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\renderers\ApiRenderer as BaseApiRenderer;
use yii\base\ViewContextInterface;
use yii\helpers\Console;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\web\AssetManager;
use yii\web\View;
use Yii;


/**
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ApiRenderer extends BaseApiRenderer implements ViewContextInterface
{
    use RendererTrait;

    public $layout = '@yii/apidoc/templates/markdown/layouts/api.php';
    public $typeView = '@yii/apidoc/templates/markdown/views/type.php';
    public $indexView = '@yii/apidoc/templates/markdown/views/index.php';

        /**
     * @var View
     */
    private $_view;
    /**
     * @var string
     */
    private $_targetDir;

        /**
     * @return View the view instance
     */
    public function getView()
    {
        if ($this->_view === null) {
            $this->_view = new View();
        }

        return $this->_view;
    }

    /**
     * @inheritdoc
     */
    public function render($context, $targetDir)
    {
        $this->apiContext = $context;
        $this->_targetDir = $targetDir;

        $types = array_merge($context->classes, $context->interfaces, $context->traits);
        $typeCount = count($types) + 1;

        if ($this->controller !== null) {
            Console::startProgress(0, $typeCount, 'Rendering files: ', false);
        }
        $done = 0;
        foreach ($types as $type) {
            $fileContent = $this->renderWithLayout($this->typeView, [
                'type' => $type,
                'apiContext' => $context,
                'types' => $types,
            ]);

            $filename = $this->generateFileName($type->name);
            $path = $targetDir . '/' . $filename;
            
            $parts = explode('/', $filename);
            
            if ( ! is_dir(dirname($path)))
            {
                mkdir(dirname($path), 0777, true);
            }

            file_put_contents($path, $fileContent);

            if ($this->controller !== null) {
                Console::updateProgress(++$done, $typeCount);
            }
        }

        $indexFileContent = $this->renderWithLayout($this->indexView, [
            'apiContext' => $context,
            'types' => $types,
        ]);
        file_put_contents($targetDir . '/index.md', $indexFileContent);

        if ($this->controller !== null) {
            Console::updateProgress(++$done, $typeCount);
            Console::endProgress(true);
            $this->controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
        }
    }

        /**
     * Renders file applying layout
     * @param string $viewFile the view name
     * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
     * @return string
     */
    protected function renderWithLayout($viewFile, $params)
    {
        $output = $this->getView()->render($viewFile, $params, $this);
        if ($this->layout !== false) {
            $params['content'] = $output;
            return $this->getView()->renderFile($this->layout, $params, $this);
        }

        return $output;
    }


    /**
     * @inheritdoc
     */
    public function getSourceUrl($type, $line = null)
    {
        if (is_string($type)) {
            $type = $this->apiContext->getType($type);
        }

        switch ($this->getTypeCategory($type)) {
            case 'yii':
                $baseUrl = 'https://github.com/yiisoft/yii2/blob/master';
                if ($type->name == 'Yii') {
                    $url = "$baseUrl/framework/Yii.php";
                } else {
                    $url = "$baseUrl/framework/" . str_replace('\\', '/', substr($type->name, 4)) . '.php';
                }
                break;
            case 'app':

                $baseUrl = 'https://github.com/workingconcept/snipcart-craft-plugin/';

                $url = $baseUrl . str_replace('\\', '/', substr($type->name, 15)) . '.php';
                $url = str_replace('snipcart-craft-plugin/snipcart/', 'snipcart-craft-plugin/tree/master/src/', $url);

                if ($type->namespace === 'workingconcept\snipcart\models')
                {
                    $exceptions = [
                        'Settings',
                        'ShippingQuoteLog',
                        'WebhookLog',
                    ];

                    $className = str_replace($type->namespace . '\\', '', $type->name);

                    if (! in_array($className, $exceptions))
                    {
                        $url = str_replace('/models/', '/models/snipcart/', $url);
                    }
                }

                break;
            default:
                $parts = explode('\\', substr($type->name, 4));
                $ext = $parts[0];
                unset($parts[0]);
                $url = "https://github.com/yiisoft/yii2-$ext/blob/master/" . implode('/', $parts) . '.php';
                break;
        }

        if ($line === null) {
            return $url;
        }
        return $url . '#L' . $line;
    }

    /**
     * @inheritdoc
     */
    public function generateApiUrl($typeName)
    {
        return $this->generateFileName($typeName);
    }

    /**
     * Generates file name for API page for a given type
     * @param string $typeName
     * @return string
     */
    protected function generateFileName($typeName)
    {
        $filename = strtolower(str_replace('\\', '-', $typeName)) . '.md';
        $filename = str_replace('workingconcept-snipcart-', '', $filename);

        $parts = explode('-', $filename);

        $first = array_shift($parts);

        $filename = $first . '/' . implode('-', $parts);
        $filename = rtrim($filename, '/');

        return $filename;
    }

    /**
     * @inheritdoc
     */
    public function getViewPath()
    {
        return Yii::getAlias('@yii/apidoc/templates/markdown/views');
    }

    /**
     * @inheritdoc
     */
    protected function generateLink($text, $href, $options = [])
    {
        $options['href'] = $href;

        //return sprintf('[%s](%s)', $text, $href);

        return Html::a($text, null, $options);
    }

        /**
     * @param ClassDoc $class
     * @return string
     */
    public function renderInheritance($class)
    {
        $parents = [];
        $skip = [
            'workingconcept',
            'snipcart'
        ];
        $parents[] = $this->createTypeLink($class);
        while ($class->parentClass !== null && ! in_array($class->parentClass, $skip)) {
            if (isset($this->apiContext->classes[$class->parentClass])) {
                $class = $this->apiContext->classes[$class->parentClass];
                $parents[] = $this->createTypeLink($class);
            } else {
                $parents[] = $this->createTypeLink($class->parentClass);
                break;
            }
        }

        return implode(" &raquo;\n", $parents);
    }

    /**
     * @param array $names
     * @return string
     */
    public function renderInterfaces($names)
    {
        $interfaces = [];
        sort($names, SORT_STRING);
        foreach ($names as $interface) {
            if (isset($this->apiContext->interfaces[$interface])) {
                $interfaces[] = $this->createTypeLink($this->apiContext->interfaces[$interface]);
            } else {
                $interfaces[] = $this->createTypeLink($interface);
            }
        }

        return implode(', ', $interfaces);
    }

    /**
     * @param array $names
     * @return string
     */
    public function renderTraits($names)
    {
        $traits = [];
        sort($names, SORT_STRING);
        foreach ($names as $trait) {
            if (isset($this->apiContext->traits[$trait])) {
                $traits[] = $this->createTypeLink($this->apiContext->traits[$trait]);
            } else {
                $traits[] = $this->createTypeLink($trait);
            }
        }

        return implode(', ', $traits);
    }

    /**
     * @param PropertyDoc $property
     * @param mixed $context
     * @return string
     */
    public function renderPropertySignature($property, $context = null)
    {
        if ($property->getter !== null || $property->setter !== null) {
            $sig = [];
            if ($property->getter !== null) {
                $sig[] = $this->renderMethodSignature($property->getter, $context);
            }
            if ($property->setter !== null) {
                $sig[] = $this->renderMethodSignature($property->setter, $context);
            }

            return implode('<br />', $sig);
        }

        $definition = [];
        $definition[] = $property->visibility;
        if ($property->isStatic) {
            $definition[] = 'static';
        }

        return '<span class="signature-defs">' . implode(' ', $definition) . '</span> '
            . '<span class="signature-type">' . $this->createTypeLink($property->types, $context) . '</span>'
            . ' ' . $this->createSubjectLink($property, $property->name) . ' '
            . ApiMarkdown::highlight('= ' . $this->renderDefaultValue($property->defaultValue), 'php');
    }



    /**
     * @param MethodDoc $method
     * @return string
     */
    public function renderMethodSignature($method, $context = null)
    {
        $params = [];
        foreach ($method->params as $param) {
            $params[] = (empty($param->typeHint) ? '' : '<span class="signature-type">' . $this->createTypeLink($param->typeHint, $context) . '</span> ')
                . ($param->isPassedByReference ? '<b>&</b>' : '')
                . ApiMarkdown::highlight(
                    $param->name
                    . ($param->isOptional ? ' = ' . $this->renderDefaultValue($param->defaultValue) : ''),
                    'php'
                );
        }

        $definition = [];
        $definition[] = $method->visibility;
        if ($method->isAbstract) {
            $definition[] = 'abstract';
        }
        if ($method->isStatic) {
            $definition[] = 'static';
        }

        return '<span class="signature-defs">' . implode(' ', $definition) . '</span> '
            . '<span class="signature-type">' . ($method->isReturnByReference ? '<b>&</b>' : '')
            . ($method->returnType === null ? 'void' : $this->createTypeLink($method->returnTypes, $context)) . '</span> '
            . '<strong>' . $this->createSubjectLink($method, $method->name) . '</strong>'
            . str_replace('  ', ' ', ' ( ' . implode(', ', $params) . ' )');
    }

    /**
     * Renders the default value.
     * @param mixed $value
     * @return string
     * @since 2.1.1
     */
    public function renderDefaultValue($value)
    {
        if ($value === null) {
            return 'null';
        }

        // special numbers which are usually used in octal or hex notation
        static $specials = [
            // file permissions
            '420' => '0644',
            '436' => '0664',
            '438' => '0666',
            '493' => '0755',
            '509' => '0775',
            '511' => '0777',
            // colors used in yii\captcha\CaptchaAction
            '2113696' => '0x2040A0',
            '16777215' => '0xFFFFFF',
        ];
        if (isset($specials[$value])) {
            return $specials[$value];
        }

        return $value;
    }


    /**
     * @param array $names
     * @return string
     */
    public function renderClasses($names)
    {
        $classes = [];
        sort($names, SORT_STRING);
        foreach ($names as $class) {
            if (isset($this->apiContext->classes[$class])) {
                $classes[] = $this->createTypeLink($this->apiContext->classes[$class]);
            } else {
                $classes[] = $this->createTypeLink($class);
            }
        }

        return implode(', ', $classes);
    }


        /**
     * creates a link to a type (class, interface or trait)
     * @param ClassDoc|InterfaceDoc|TraitDoc|ClassDoc[]|InterfaceDoc[]|TraitDoc[]|string|string[] $types
     * @param string $title a title to be used for the link TODO check whether [[yii\...|Class]] is supported
     * @param BaseDoc $context
     * @param array $options additional HTML attributes for the link.
     * @return string
     */
    public function createTypeLink($types, $context = null, $title = null, $options = [])
    {
        if (!is_array($types)) {
            $types = [$types];
        }
        if (count($types) > 1) {
            $title = null;
        }
        $links = [];
        foreach ($types as $type) {
            $postfix = '';
            if (is_string($type)) {
                if (!empty($type) && substr_compare($type, '[]', -2, 2) === 0) {
                    $postfix = '[]';
                    $type = substr($type, 0, -2);
                }

                if ($type === '$this' && $context instanceof TypeDoc) {
                    $title = '$this';
                    $type = $context;
                } elseif (($t = $this->apiContext->getType(ltrim($type, '\\'))) !== null) {
                    $type = $t;
                } elseif (!empty($type) && $type[0] !== '\\' && ($t = $this->apiContext->getType($this->resolveNamespace($context) . '\\' . ltrim($type, '\\'))) !== null) {
                    $type = $t;
                } else {
                    ltrim($type, '\\');
                }
            }
            if (is_string($type)) {
                $linkText = ltrim($type, '\\');
                if ($title !== null) {
                    $linkText = $title;
                    $title = null;
                }
                $phpTypes = [
                    'callable',
                    'array',
                    'string',
                    'boolean',
                    'bool',
                    'integer',
                    'int',
                    'float',
                    'object',
                    'resource',
                    'null',
                    'false',
                    'true',
                ];
                $phpTypeAliases = [
                    'true' => 'boolean',
                    'false' => 'boolean',
                    'bool' => 'boolean',
                    'int' => 'integer',
                ];
                $phpTypeDisplayAliases = [
                    'bool' => 'boolean',
                    'int' => 'integer',
                ];
                // check if it is PHP internal class
                if (((class_exists($type, false) || interface_exists($type, false) || trait_exists($type, false)) &&
                    ($reflection = new \ReflectionClass($type)) && $reflection->isInternal())) {
                    $links[] = $this->generateLink($linkText, 'http://www.php.net/class.' . strtolower(ltrim($type, '\\')), $options) . $postfix;
                } elseif (in_array($type, $phpTypes)) {
                    if (isset($phpTypeDisplayAliases[$type])) {
                        $linkText = $phpTypeDisplayAliases[$type];
                    }
                    if (isset($phpTypeAliases[$type])) {
                        $type = $phpTypeAliases[$type];
                    }
                    $links[] = $this->generateLink($linkText, 'http://www.php.net/language.types.' . strtolower(ltrim($type, '\\')), $options) . $postfix;
                } else {
                    $links[] = $type . $postfix;
                }
            } elseif ($type instanceof BaseDoc) {
                $linkText = $type->name;
                if ($title !== null) {
                    $linkText = $title;
                    $title = null;
                }
                $links[] = $this->generateLink($linkText, $this->generateApiUrl($type->name), $options) . $postfix;
            }
        }

        return implode('|', $links);
    }


    /**
     * @param BaseDoc|string $context
     * @return string
     */
    private function resolveNamespace($context)
    {
        // TODO use phpdoc Context for this
        if ($context === null) {
            return '';
        }
        if ($context instanceof TypeDoc) {
            return $context->namespace;
        }
        if ($context->hasProperty('definedBy')) {
            $type = $this->apiContext->getType($context);
            if ($type !== null) {
                return $type->namespace;
            }
        }

        return '';
    }



}
