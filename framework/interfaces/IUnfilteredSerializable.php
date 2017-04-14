<?php
	/**
	 * Interface for use with JSONTools and XMLTools
	 * 
	 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
	 * @version 1.0
	 * @package framework
	 */

	namespace CheeseBurgames\MFX;

	/**
	 * Interface used to prevent objects implementing it from being filtered by JSONTools::filterAndEncode() and XMLTools::build()
	 * 
	 * @see JSONTools::filterAndEncode()
	 * @see XMLTools::build()
	 */
	interface IUnfilteredSerializable { }
	