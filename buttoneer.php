<?php
/**
 * @package     PKForms Manager
 * @subpackage  plg.buttoneer
 *
 * @copyright   Copyright (C) 2016 PKWARE, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
/**
 * Plug-in to enable adding a button to content using a property of the content
 * This gets triggered with a syntax of {osapply | pkapply}
 */
class PlgContentButtoneer extends JPlugin
{
	/**
	 * Load the language file on instantiation. Note this is only available in
	 * Joomla 3.1 and higher, which is not a problem for us.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;
	/**
	 * Lookup table for the method each token uses to process the title.
	 *
	 * @var    array
	 */
	protected $formatters = array();
	/**
	 *	Load the lookup table with the anonymous functions to format the titles,
	 *	then call the parent.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    Optional associative array of configuration settings.
	 */
	public function __construct(&$subject, $config = array())
	{
		$this->formatters['{osapply}'] =
			function($title) { return preg_replace("/\s+/", "_", $title); };
		$this->formatters['{pkapply}'] =
			function($title) { return base64_encode($title); };
		parent::__construct($subject, $config);
	}
	/**
	 * Plugin that inserts a button within content
	 *
	 * @param   string   $context   The context of the content being passed to the plugin.
	 * @param   object   &$article  Can be any object with both text and title attributes.
	 * @param   mixed    &$params   The article params	(not used here)
	 * @param   integer  $page      The 'page' number	(not used here)
	 *
	 * @return  void
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		if ($this->_shouldNotTry($context, $article))
		{
			return;
		}
		
		foreach ($this->formatters as $token => $formatter)
		{
			$base = preg_replace('/[\{\}]/', '', $token);
			$article->text = str_replace($token, 
				$this->_buttonText($article->title, $base, $formatter), $article->text);
		}
	}
	/**
	 * Checks to see if the plugin has all the correct conditions.
	 *
	 * @param	string	$context	The context in which the plugin has been called
	 * @param	object	$article	The object passed in to the plugin
	 *
	 * @return	boolean	true if should not operate under these conditions
	 */
	private function _shouldNotTry( $context, $article )
	{
		$allowed_contexts = array(	'com_content.category', 
									'com_content.article', 
									'com_content.featured');
		if (empty($article->text) ||
			empty($article->title) ||
			!in_array($context, $allowed_contexts))
		{
			return true;
		}
		return false;
	}
	/**
	 * Builds the text for the button by processing its base text.
	 *
	 * @param	string	$title		The title to be used in the button
	 * @param	string	$base		The base text from the plugin param
	 * @param	string	$formatter	The method to use to make the title ready to be used
	 *
	 * @return	string
	 */
	private function _buttonText($title, $base, $formatter)
	{
		$oldTitle = $formatter($title);
		$baseText = $this->params->get($base);
		return str_replace('{title}', $oldTitle, $baseText);
	}
}