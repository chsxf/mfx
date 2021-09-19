<?php
/**
 * Data validator extension for Twig
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @version 1.0
 */

namespace chsxf\MFX\DataValidator\Twig;

use chsxf\MFX\DataValidator;

/**
 * Data validator Twig extension class
 */
class Extension extends \Twig_Extension
{
	/**
	 * (non-PHPdoc)
	 * @see \Twig_ExtensionInterface::getName()
	 */
	public function getName() {
		return __CLASS__;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Twig_Extension::getFunctions()
	 */
	public function getFunctions() {
		return array(
				new \Twig_SimpleFunction('dv_value', array(&$this, 'getFieldValue')),
				new \Twig_SimpleFunction('dv_indexed_value', array(&$this, 'getIndexedFieldValue'))
		);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Twig_Extension::getTokenParsers()
	 */
	public function getTokenParsers() {
		return array(
				new DataValidator_FieldTokenParser(),
				new DataValidator_FieldGroupTokenParser(),
				new DataValidator_ResetCountersTokenParser()
		);
	}
	
	/**
	 * Gets a field's value from a DataValidator
	 * @param DataValidator $validator
	 * @param string $fieldName
	 * @return mixed
	 */
	public function getFieldValue(DataValidator $validator, $fieldName) {
		return $validator->getFieldValue($fieldName, true);
	}
	
	/**
	 * Gets a field's value at index from a DataValidator
	 * @param DataValidator $validator
	 * @param string $fieldName
	 * @param int $index
	 * @return mixed
	 */
	public function getIndexedFieldValue(DataValidator $validator, $fieldName, $index) {
		return $validator->getIndexedFieldValue($fieldName, $index, true);
	}
}

/**
 * Data validator reset counters Twig token parser
 */
class DataValidator_ResetCountersTokenParser extends \Twig_TokenParser
{
	/**
	 * (non-PHPdoc)
	 * @see \Twig_TokenParserInterface::parse()
	 */
	public function parse(\Twig_Token $token) {
		$stream = $this->parser->getStream();
		
		$validatorName = $stream->expect(\Twig_Token::NAME_TYPE)->getValue();
		$stream->expect(\Twig_Token::BLOCK_END_TYPE);
		
		return new DataValidator_ResetCountersToken($validatorName, $token->getLine(), $this->getTag());
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Twig_TokenParserInterface::getTag()
	 */
	public function getTag() {
		return 'dv_reset_counters';
	}
}

/**
 * Data validator reset counters Twig token
 */
class DataValidator_ResetCountersToken extends \Twig_Node
{
	/**
	 * Constructor
	 * @param string $validatorName Validator's name in Twig context
	 * @param int $line Line number of this node
	 * @param string $tag Tag for this node
	 */
	public function __construct($validatorName, $line, $tag) {
		parent::__construct(array(), array('validatorName' => $validatorName), $line, $tag);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Twig_Node::compile()
	 */
	public function compile(\Twig_Compiler $compiler) {
		$code = sprintf("\$context['%s']->resetRepeatCounters()", $this->getAttribute('validatorName'));
		
		$compiler
			->addDebugInfo($this)
			->write($code)
			->raw(";\n");
	}
}

/**
 * Data validator field Twig token parser
 */
class DataValidator_FieldTokenParser extends \Twig_TokenParser
{
	/**
	 * (non-PHPdoc)
	 * @see \Twig_TokenParserInterface::parse()
	 * 
	 * @param \Twig_Token $token
	 */
	public function parse(\Twig_Token $token) {
		$stream = $this->parser->getStream();
		
		$validatorName = $stream->expect(\Twig_Token::NAME_TYPE)->getValue();
		$fieldName = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();
		
		$idIsString = true;
		$typeOverride = NULL;
		$currentToken = $stream->getCurrent();
		switch ($currentToken->getType())
		{
			case \Twig_Token::NAME_TYPE:
				$idIsString = false;
			case \Twig_Token::STRING_TYPE:
				$id = $stream->expect($currentToken->getType())->getValue();
				
				$currentToken = $stream->getCurrent();
				if ($currentToken->getType() == \Twig_Token::STRING_TYPE)
					$typeOverride = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();
				break;
				
			default:
				$id = NULL;
				break;
		}

		$stream->expect(\Twig_Token::BLOCK_END_TYPE);
		
		if ($id === NULL) {
			$id = $fieldName;
		}
					
		return new DataValidator_FieldNode($validatorName, $fieldName, $id, $idIsString, $typeOverride, $token->getLine(), $this->getTag());
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Twig_TokenParserInterface::getTag()
	 */
	public function getTag() {
		return 'dv_field';
	}
}

/**
 * Data validator field group Twig token parser
 */
class DataValidator_FieldGroupTokenParser extends \Twig_TokenParser
{
	/**
	 * (non-PHPdoc)
	 * @see \Twig_TokenParserInterface::parse()
	 * @param \Twig_Token $token
	 */
	public function parse(\Twig_Token $token) {
		$stream = $this->parser->getStream();
		
		$validatorName = $stream->expect(\Twig_Token::NAME_TYPE)->getValue();
		
		$nameIsString = true;
		$currentToken = $stream->getCurrent();
		switch ($currentToken->getType())
		{
			case \Twig_Token::NAME_TYPE:
				$nameIsString = false;
			case \Twig_Token::STRING_TYPE:
				$groupName = $stream->expect($currentToken->getType())->getValue();
				break;
				
			default:
				$groupName = NULL;
				break;
		}
		$stream->expect(\Twig_Token::BLOCK_END_TYPE);
		
		$body = $this->parser->subparse(array($this, 'decideGroupEnd'), true);
		$stream->expect(\Twig_Token::BLOCK_END_TYPE);
		
		return new DataValidator_FieldGroupNode($validatorName, $groupName, $nameIsString, $body, $token->getLine(), $this->getTag());
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Twig_TokenParserInterface::getTag()
	 */
	public function getTag() {
		return 'dv_field_group';
	}
	
	/**
	 * Tells if the current token is 
	 * @param \Twig_Token $token
	 * @return boolean
	 */
	public function decideGroupEnd(\Twig_Token $token) {
		return $token->test('end_dv_field_group');
	}
}

/**
 * Data validator field Twig node
 */
class DataValidator_FieldNode extends \Twig_Node
{
	/**
	 * Constructor
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
	 * @see \Twig_Node::compile()
	 */
	public function compile(\Twig_Compiler $compiler)
	{
		if ($this->getAttribute('typeOverride') !== NULL)
			$code1 = sprintf("\$fieldResult = \$context['%s']->generate('%s', new \\CheeseBurgames\\MFX\\DataValidator\\FieldType('%s'))",
					$this->getAttribute('validatorName'),
					$this->getAttribute('fieldName'),
					$this->getAttribute('typeOverride'));
		else
			$code1 = sprintf("\$fieldResult = \$context['%s']->generate('%s')",
							$this->getAttribute('validatorName'),
							$this->getAttribute('fieldName'));
		
		if ($this->getAttribute('idIsString'))
			$code2 = sprintf("\$fieldResult[1] = array_merge(array(
										'id' => '%s'
								), \$fieldResult[1])",
					$this->getAttribute('id'));
		else
			$code2 = sprintf("\$fieldResult[1] = array_merge(array(
										'id' => \$context['%s']
								), \$fieldResult[1])",
					$this->getAttribute('id'));
		
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
 */
class DataValidator_FieldGroupNode extends \Twig_Node
{
	/**
	 * Constructor
	 * @param string $validatorName Validator's name in Twig context
	 * @param string $groupName Group's name
	 * @param bool $nameIsString If set, the group name is stored as a raw string
	 * @param \Twig_Node $body Body node
	 * @param int $line Line number of this node
	 * @param string $tag Tag for this node
	 */
	public function __construct($validatorName, $groupName, $nameIsString, \Twig_Node $body, $line, $tag)
	{
		parent::__construct(array( 'body' => $body ), array(
				'validatorName' => $validatorName,
				'groupName' => $groupName,
				'nameIsString' => !empty($nameIsString)
		), $line, $tag);
	}

	/**
	 * (non-PHPdoc)
	 * @see \Twig_Node::compile()
	 */
	public function compile(\Twig_Compiler $compiler)
	{
		if ($this->getAttribute('nameIsString'))
			$code = "'{$this->getAttribute('groupName')}'";
		else
			$code = "\$context['{$this->getAttribute('groupName')}']";
		
		$compiler->addDebugInfo($this)
			->write("\$context['{$this->getAttribute('validatorName')}']->pushGenerationGroup({$code});")->raw("\n")
			->subcompile($this->getNode('body'))
			->write("\$context['{$this->getAttribute('validatorName')}']->popGenerationGroup();")->raw("\n");
	}
}