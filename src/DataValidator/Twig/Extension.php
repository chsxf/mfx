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

        return new DataValidator_ResetCountersToken($validatorName, $token->getLine(), $this->getTag());
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
    /**
     * Constructor
     * @since 1.0
     * @param string $validatorName Validator's name in Twig context
     * @param int $line Line number of this node
     * @param string $tag Tag for this node
     */
    public function __construct($validatorName, $line, $tag)
    {
        parent::__construct(array(), array('validatorName' => $validatorName), $line, $tag);
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see \Twig_Node::compile()
     */
    public function compile(Compiler $compiler)
    {
        $code = sprintf("\$context['%s']->resetRepeatCounters()", $this->getAttribute('validatorName'));

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
        $fieldName = $stream->expect(Token::STRING_TYPE)->getValue();

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

        return new DataValidator_FieldNode($validatorName, $fieldName, $id, $idIsString, $typeOverride, $token->getLine(), $this->getTag());
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

        return new DataValidator_FieldGroupNode($validatorName, $groupName, $nameIsString, $body, $token->getLine(), $this->getTag());
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
 * Data validator field Twig node
 * @since 1.0
 */
class DataValidator_FieldNode extends Node
{
    /**
     * Constructor
     * @since 1.0
     * @param string $validatorName Validator's name in Twig context
     * @param string $fieldName Field's name
     * @param string $id Field's ID for the HTML output
     * @param bool $idIsString If set, the field's ID is stored as a raw string
     * @param string $typeOverride Field type to use to override the initial field type. If NULL, no override.
     * @param int $line Line number of this node
     * @param string $tag Tag for this node
     */
    public function __construct($validatorName, $fieldName, $id, $idIsString, $typeOverride, $line, $tag)
    {
        parent::__construct(array(), array(
            'validatorName' => $validatorName,
            'fieldName' => $fieldName,
            'id' => $id,
            'idIsString' => !empty($idIsString),
            'typeOverride' => $typeOverride
        ), $line, $tag);
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see \Twig_Node::compile()
     */
    public function compile(Compiler $compiler)
    {
        if ($this->getAttribute('typeOverride') !== null) {
            $code1 = sprintf(
                "\$fieldResult = \$context['%s']->generate('%s', new \\CheeseBurgames\\MFX\\DataValidator\\FieldType('%s'))",
                $this->getAttribute('validatorName'),
                $this->getAttribute('fieldName'),
                $this->getAttribute('typeOverride')
            );
        } else {
            $code1 = sprintf(
                "\$fieldResult = \$context['%s']->generate('%s')",
                $this->getAttribute('validatorName'),
                $this->getAttribute('fieldName')
            );
        }

        if ($this->getAttribute('idIsString')) {
            $code2 = sprintf(
                "\$fieldResult[1] = array_merge(array( 'id' => '%s' ), \$fieldResult[1])",
                $this->getAttribute('id')
            );
        } else {
            $code2 = sprintf(
                "\$fieldResult[1] = array_merge(array( 'id' => \$context['%s'] ), \$fieldResult[1])",
                $this->getAttribute('id')
            );
        }

        $code3 = sprintf('$this->env->display($fieldResult[0], $fieldResult[1])');

        $compiler
            ->addDebugInfo($this)
            ->write($code1)
            ->raw(";\n")
            ->write($code2)
            ->raw(";\n")
            ->write($code3)
            ->raw(";\n");
    }
}

/**
 * Data validator field group Twig node
 * @since 1.0
 */
class DataValidator_FieldGroupNode extends Node
{
    /**
     * Constructor
     * @since 1.0
     * @param string $validatorName Validator's name in Twig context
     * @param string $groupName Group's name
     * @param bool $nameIsString If set, the group name is stored as a raw string
     * @param \Twig_Node $body Body node
     * @param int $line Line number of this node
     * @param string $tag Tag for this node
     */
    public function __construct($validatorName, $groupName, $nameIsString, Node $body, $line, $tag)
    {
        parent::__construct(array('body' => $body), array(
            'validatorName' => $validatorName,
            'groupName' => $groupName,
            'nameIsString' => !empty($nameIsString)
        ), $line, $tag);
    }

    /**
     * (non-PHPdoc)
     * @ignore
     * @see \Twig_Node::compile()
     */
    public function compile(Compiler $compiler)
    {
        if ($this->getAttribute('nameIsString')) {
            $code = "'{$this->getAttribute('groupName')}'";
        } else {
            $code = "\$context['{$this->getAttribute('groupName')}']";
        }

        $compiler->addDebugInfo($this)
            ->write("\$context['{$this->getAttribute('validatorName')}']->pushGenerationGroup({$code});")->raw("\n")
            ->subcompile($this->getNode('body'))
            ->write("\$context['{$this->getAttribute('validatorName')}']->popGenerationGroup();")->raw("\n");
    }
}
