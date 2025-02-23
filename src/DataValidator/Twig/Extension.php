<?php

declare(strict_types=1);

/**
 * Data validator extension for Twig
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator\Twig;

use chsxf\MFX\DataValidator;
use Twig\Compiler;
use Twig\Extension\AbstractExtension;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;
use Twig\TwigFunction;

/**
 * Data validator Twig extension class
 * @since 1.0
 */
class Extension extends AbstractExtension
{
    /**
     * (non-PHPdoc)
     * @ignore
     * @see \Twig_ExtensionInterface::getName()
     */
    public function getName()
    {
        return __CLASS__;
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see \Twig_Extension::getFunctions()
     */
    public function getFunctions()
    {
        return array(
            new TwigFunction('dv_value', array(&$this, 'getFieldValue')),
            new TwigFunction('dv_indexed_value', array(&$this, 'getIndexedFieldValue'))
        );
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see \Twig_Extension::getTokenParsers()
     */
    public function getTokenParsers()
    {
        return array(
            new DataValidator_FieldTokenParser(),
            new DataValidator_FieldGroupTokenParser(),
            new DataValidator_ResetCountersTokenParser()
        );
    }

    /**
     * Gets a field's value from a DataValidator
     * @since 1.0
     * @param DataValidator $validator
     * @param string $fieldName
     * @return mixed
     */
    public function getFieldValue(DataValidator $validator, $fieldName)
    {
        return $validator->getFieldValue($fieldName, true);
    }

    /**
     * Gets a field's value at index from a DataValidator
     * @since 1.0
     * @param DataValidator $validator
     * @param string $fieldName
     * @param int $index
     * @return mixed
     */
    public function getIndexedFieldValue(DataValidator $validator, $fieldName, $index)
    {
        return $validator->getIndexedFieldValue($fieldName, $index, true);
    }
}

/**
 * Data validator reset counters Twig token parser
 * @since 1.0
 */
class DataValidator_ResetCountersTokenParser extends AbstractTokenParser
{
    /**
     * (non-PHPdoc)
     * @ignore
     * @see \Twig_TokenParserInterface::parse()
     */
    public function parse(Token $token)
    {
        $stream = $this->parser->getStream();

        $validatorName = $stream->expect(Token::NAME_TYPE)->getValue();
        $stream->expect(Token::BLOCK_END_TYPE);

        return new DataValidator_ResetCountersToken($validatorName, $token->getLine());
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see \Twig_TokenParserInterface::getTag()
     */
    public function getTag()
    {
        return 'dv_reset_counters';
    }
}

/**
 * Data validator reset counters Twig token
 * @since 1.0
 */
class DataValidator_ResetCountersToken extends Node
{
    private const string VALIDATOR_NAME = 'validatorName';

    /**
     * Constructor
     * @since 1.0
     * @param string $validatorName Validator's name in Twig context
     * @param int $line Line number of this node
     * @param string $tag Tag for this node
     */
    public function __construct(string $validatorName, int $line)
    {
        parent::__construct(array(), [self::VALIDATOR_NAME => $validatorName], $line);
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see \Twig_Node::compile()
     */
    public function compile(Compiler $compiler)
    {
        $code = sprintf("\$context['%s']->resetRepeatCounters()", $this->getAttribute(self::VALIDATOR_NAME));

        $compiler
            ->addDebugInfo($this)
            ->write($code)
            ->raw(";\n");
    }
}

/**
 * Data validator field Twig token parser
 * @since 1.0
 */
class DataValidator_FieldTokenParser extends AbstractTokenParser
{
    /**
     * (non-PHPdoc)
     * @ignore
     * @see \Twig_TokenParserInterface::parse()
     *
     * @param \Twig_Token $token
     */
    public function parse(Token $token)
    {
        $stream = $this->parser->getStream();

        $validatorName = $stream->expect(Token::NAME_TYPE)->getValue();

        $fieldNameIsString = false;
        if ($stream->test(Token::NAME_TYPE)) {
            $fieldName = $stream->expect(Token::NAME_TYPE)->getValue();
        } else {
            $fieldName = $stream->expect(Token::STRING_TYPE)->getValue();
            $fieldNameIsString = true;
        }

        $idIsString = true;
        $typeOverride = null;
        $currentToken = $stream->getCurrent();
        switch ($currentToken->getType()) {
            case Token::NAME_TYPE:
                $idIsString = false;
                // no break
            case Token::STRING_TYPE:
                $id = $stream->expect($currentToken->getType())->getValue();

                $currentToken = $stream->getCurrent();
                if ($currentToken->getType() == Token::STRING_TYPE) {
                    $typeOverride = $stream->expect(Token::STRING_TYPE)->getValue();
                }
                break;

            default:
                $id = null;
                break;
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        if ($id === null) {
            $id = $fieldName;
        }

        return new DataValidator_FieldNode($validatorName, $fieldName, $fieldNameIsString, $id, $idIsString, $typeOverride, $token->getLine());
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see \Twig_TokenParserInterface::getTag()
     */
    public function getTag()
    {
        return 'dv_field';
    }
}

/**
 * Data validator field Twig node
 * @since 1.0
 */
class DataValidator_FieldNode extends Node
{
    private const string VALIDATOR_NAME = 'validatorName';
    private const string FIELD_NAME = 'fieldName';
    private const string FIELD_NAME_IS_STRING = 'fieldNameIsString';
    private const string ID = 'id';
    private const string ID_IS_STRING = 'idIsString';
    private const string TYPE_OVERRIDE = 'typeOverride';

    /**
     * Constructor
     * @since 1.0
     * @param string $validatorName Validator's name in Twig context
     * @param string $fieldName Field's name
     * @param bool $fieldNameIsString If set, the field name is stored as a raw string
     * @param string $id Field's ID for the HTML output
     * @param bool $idIsString If set, the field's ID is stored as a raw string
     * @param string $typeOverride Field type to use to override the initial field type. If NULL, no override.
     * @param int $line Line number of this node
     */
    public function __construct(string $validatorName, string $fieldName, bool $fieldNameIsString, string $id, bool $idIsString, ?string $typeOverride, int $line)
    {
        parent::__construct([], [
            self::VALIDATOR_NAME => $validatorName,
            self::FIELD_NAME => $fieldName,
            self::FIELD_NAME_IS_STRING => !empty($fieldNameIsString),
            self::ID => $id,
            self::ID_IS_STRING => !empty($idIsString),
            self::TYPE_OVERRIDE => $typeOverride
        ], $line);
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see \Twig_Node::compile()
     */
    public function compile(Compiler $compiler)
    {
        $fieldName = $this->getAttribute(self::FIELD_NAME);
        if ($this->getAttribute(self::FIELD_NAME_IS_STRING)) {
            $code1 = sprintf("\$fieldName = '%s'", $fieldName);
        } else {
            $code1 = sprintf("\$fieldName = \$context['%s']", $fieldName);
        }

        $validatorName = $this->getAttribute(self::VALIDATOR_NAME);
        $typeOverride = $this->getAttribute(self::TYPE_OVERRIDE);
        if ($typeOverride !== null) {
            $code2 = sprintf(
                "\$fieldResult = \$context['%s']->generate(\$fieldName, \\chsxf\\MFX\\DataValidator\\FieldType::from('%s'))",
                $validatorName,
                $typeOverride
            );
        } else {
            $code2 = sprintf("\$fieldResult = \$context['%s']->generate(\$fieldName)", $validatorName);
        }

        $id = $this->getAttribute(self::ID);
        if ($this->getAttribute(self::ID_IS_STRING)) {
            $code3 = sprintf("\$fieldResult[1] = array_merge(array( 'id' => '%s' ), \$fieldResult[1])", $id);
        } else {
            $code3 = sprintf("\$fieldResult[1] = array_merge(array( 'id' => \$context['%s'] ), \$fieldResult[1])", $id);
        }

        $code4 = sprintf('$this->env->display($fieldResult[0], $fieldResult[1])');

        $compiler
            ->addDebugInfo($this)
            ->write($code1)
            ->raw(";\n")
            ->write($code2)
            ->raw(";\n")
            ->write($code3)
            ->raw(";\n")
            ->write($code4)
            ->raw(";\n");
    }
}

/**
 * Data validator field group Twig token parser
 * @since 1.0
 */
class DataValidator_FieldGroupTokenParser extends AbstractTokenParser
{
    /**
     * (non-PHPdoc)
     * @ignore
     * @see \Twig_TokenParserInterface::parse()
     * @param \Twig_Token $token
     */
    public function parse(Token $token)
    {
        $stream = $this->parser->getStream();

        $validatorName = $stream->expect(Token::NAME_TYPE)->getValue();

        $nameIsString = true;
        $currentToken = $stream->getCurrent();
        switch ($currentToken->getType()) {
            case Token::NAME_TYPE:
                $nameIsString = false;
                // no break
            case Token::STRING_TYPE:
                $groupName = $stream->expect($currentToken->getType())->getValue();
                break;

            default:
                $groupName = null;
                break;
        }
        $stream->expect(Token::BLOCK_END_TYPE);

        $body = $this->parser->subparse(array($this, 'decideGroupEnd'), true);
        $stream->expect(Token::BLOCK_END_TYPE);

        return new DataValidator_FieldGroupNode($validatorName, $groupName, $nameIsString, $body, $token->getLine());
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see \Twig_TokenParserInterface::getTag()
     */
    public function getTag()
    {
        return 'dv_field_group';
    }

    /**
     * Tells if the current token is
     * @since 1.0
     * @param \Twig_Token $token
     * @return boolean
     */
    public function decideGroupEnd(Token $token)
    {
        return $token->test('end_dv_field_group');
    }
}

/**
 * Data validator field group Twig node
 * @since 1.0
 */
class DataValidator_FieldGroupNode extends Node
{
    private const string BODY = 'body';
    private const string VALIDATOR_NAME = 'validatorName';
    private const string GROUP_NAME = 'groupName';
    private const string NAME_IS_STRING = 'nameIsString';

    /**
     * Constructor
     * @since 1.0
     * @param string $validatorName Validator's name in Twig context
     * @param string $groupName Group's name
     * @param bool $nameIsString If set, the group name is stored as a raw string
     * @param \Twig_Node $body Body node
     * @param int $line Line number of this node
     */
    public function __construct(string $validatorName, string $groupName, bool $nameIsString, Node $body, int $line)
    {
        parent::__construct([self::BODY => $body], [
            self::VALIDATOR_NAME => $validatorName,
            self::GROUP_NAME => $groupName,
            self::NAME_IS_STRING => !empty($nameIsString)
        ], $line);
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see \Twig_Node::compile()
     */
    public function compile(Compiler $compiler)
    {
        $groupName = $this->getAttribute(self::GROUP_NAME);
        if ($this->getAttribute(self::NAME_IS_STRING)) {
            $code = "'{$groupName}'";
        } else {
            $code = "\$context['{$groupName}']";
        }

        $validatorName = $this->getAttribute(self::VALIDATOR_NAME);
        $compiler->addDebugInfo($this)
            ->write("\$context['{$validatorName}']->pushGenerationGroup({$code});")
            ->raw("\n")
            ->subcompile($this->getNode(self::BODY))
            ->write("\$context['{$validatorName}']->popGenerationGroup();")
            ->raw("\n");
    }
}
