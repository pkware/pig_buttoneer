<?php
define('_JEXEC', "Testing");
/**
 *	First, stub out the parent class. I do this because I'm not interested in anything
 *	it does, for the purposes of this test. This test is only concerned with the code
 *	specific to the plugin. Code outside the plugin is outside the scope of this test,
 *	and should be tested at the integration, not the unit, level.
 */
class JPlugin
{
	public function __construct(&$subject, $config = array())
	{
		$this->params = new Fetcher;
	}
}
/**
 *	Created as a stub class simply to mimic the behavior of the sole member of the
 *	parent class that is being called by the tested code. For the purposes of the
 *	test doubles, all we need do is mimic the results of the behavior, not the
 *	behavior itself. That should be tested at the integration level.
 */
class Fetcher
{
	public function get($term)
	{
		switch ($term) {
			case "osapply":
			case "pkapply":
				return "<button>{title}</button>";
				break;
			default:
				return $term;
		}
	}
}
		
require_once "buttoneer.php";
/**
 *	Test the plugin
 *
 */
class plgbuttoneerTest extends \PHPUnit_Framework_TestCase
{

	public function setup()
	{
		$this->params = array();
		$this->old_article = (object) array(
				'text' => "This is the text with old {osapply} button.",
				'title' => "Old Article" );
		$this->new_article = (object) array(
				'text' => "This is the text with new {pkapply} button.",
				'title' => "New Article" );
		$this->both_article = (object) array(
				'text' => "This is the text with both {osapply} {pkapply} buttons.",
				'title' => "Timeless Article" );
		$this->plugin = new PlgContentButtoneer(new Fetcher);
	}
	public function testPluginContstructor()
	{
		$this->assertNotNull($this->plugin);
		$this->assertInstanceOf('PlgContentButtoneer', $this->plugin);
	}
	public function testOldStyleReplace()
	{
		$this->plugin->onContentPrepare(
				"com_content.article",
				$this->old_article,
				$this->params,
				NULL
		);
		$this->assertEquals(
			"This is the text with old <button>Old_Article</button> button.",
			$this->old_article->text
		);
	}
	public function testBadContext()
	{
		$original_text = $this->old_article->text;
		$this->plugin->onContentPrepare(
				"fred.article",
				$this->old_article,
				$this->params,
				NULL
		);
		$this->assertEquals($original_text, $this->old_article->text);
	}
	public function testBadText()
	{
		$this->old_article->text = "";
		$original_text = $this->old_article->text;
		$this->plugin->onContentPrepare(
				"com_content.article",
				$this->old_article,
				$this->params,
				NULL
		);
		$this->assertEquals($original_text, $this->old_article->text);
	}
	public function testBadTitle()
	{
		$this->old_article->title = "";
		$original_text = $this->old_article->text;
		$this->plugin->onContentPrepare(
				"com_content.article",
				$this->old_article,
				$this->params,
				NULL
		);
		$this->assertEquals($original_text, $this->old_article->text);
	}
	public function testNewReplace()
	{
		$coded = base64_encode($this->new_article->title);
		$this->plugin->onContentPrepare(
				"com_content.article",
				$this->new_article,
				$this->params,
				NULL
		);
		$this->assertEquals(
			"This is the text with new <button>$coded</button> button.",
			$this->new_article->text
		);
	}
	public function testBothReplace()
	{
		$coded = base64_encode($this->both_article->title);
		$this->plugin->onContentPrepare(
				"com_content.article",
				$this->both_article,
				$this->params,
				NULL
		);
		$this->assertEquals(
			"This is the text with both <button>Timeless_Article</button> <button>$coded</button> buttons.",
			$this->both_article->text
		);
	}
}