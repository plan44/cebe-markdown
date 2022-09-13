<?php
/**
 * @copyright Copyright (c) 2022 Lukas Zeller
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown;


function eval_expression($expr, $defs)
{
  foreach($defs as $name => $value) {
    $$name = $value;
  }
  $res = 'NO EVAL';
  return @eval('return ' . $expr . ' ;');
}

/**
 * Markdown parser for github flavored markdown plus p44 extensions
 *
 * @author Lukas Zeller <luz@plan44.ch>
 */
class p44Markdown extends GithubMarkdown
{
  // include inline element parsing using traits
  use inline\ColorTrait;
  use inline\UnderscoreTrait;

  protected $condstack = array();
  protected $rendering = true;
  protected $defs = array();

  public $debugconditionals = false;

  public function add_defs(array $newdefs)
  {
    $this->defs = array_merge($this->defs, $newdefs);
  }

  public function set_defs(array $newdefs)
  {
    $this->defs = $newdefs;
  }

  protected function prepare()
  {
    parent::prepare();
    $this->condstack = array(true, true); // begin with rendering on
    $this->rendering = true; // current rendering
  }

  /**
   * Parses the variable replacement and conditionals
   * @marker {%
   */
  protected function parseVariable($markdown)
  {
    if (preg_match('/^\{%\s*(.+?)\s*%\}/', $markdown, $matches)) {
      // supported syntax:
      // conditional rendering
      //   if <expr>
      //   elif,elsif,else if <expr>
      //   else
      //   endif
      // expression substitution
      //   <expr>
      $text = '';
      if (preg_match('/^(if|elif|elsif|else\s+if)\s+(.+)/', $matches[1], $m2)) {
        // if/elif expression
        $cond = (boolean) eval_expression($m2[2], $this->defs);
        $text = $m2[1] . ' (' . ($cond ? "true" : "false") . ') ';
        $block = [
          'cond',
          $m2[1]=="if" ? 1 : 0, // if adds one to the nesting level
          $cond, // condition
          $matches[1], // expression text
        ];
      }
      elseif($matches[1]=='else') {
        // else
        $block = [
          'cond',
          0,
          2, // special flag for else = invert current condition
          $matches[1]
        ];
      }
      elseif($matches[1]=='endif') {
        // endif
        $block = [
          'cond',
          -1, // endif closes one nesting level
          0,
          $matches[1]
        ];
      }
      else {
        // variable substitution
        $block = ['text', (string) eval_expression($matches[1], $this->defs)];
      }
      return [
        $block,
        strlen($matches[0])
      ];
    }
    return [['text', $markdown[0] . $markdown[1]], 2];
  }


  protected function setRenderCond($levelchange, $newcond)
  {
    $parentrendering = true;
    if ($levelchange>0) {
      // new level
      $parentrendering = $this->rendering;
      array_push($this->condstack, array($newcond, $parentrendering));
    }
    else if ($levelchange<0) {
      // end of level, restore mute of before
      list(,$this->rendering) = array_pop($this->condstack);
      return;
    }
    else {
      // chained
      list($oldcond,$parentrendering) = array_pop($this->condstack);
      if ($oldcond) {
        // previous in chain was true, all further must be false
        $newcond = false;
        $pushcond = true; // but push true to continue chain disabled
      }
      else {
        // previous in chain was false, next may be true
        if ($newcond==2) {
          // invert condition
          $newcond = !$oldcond;
        }
        $pushcond = $newcond;
      }
      array_push($this->condstack, array($pushcond, $parentrendering));
    }
    if ($parentrendering) $this->rendering = $newcond;
  }


  protected function renderCond($block)
  {
    $this->setRenderCond($block[1], $block[2]);
    if (!$this->debugconditionals) return ''; // no output
    return sprintf(
      "<pre>[%s : result=%d, levelchange=%d, rendering=%d]</pre>",
      $block[3], $block[2], $block[1], $this->rendering
    );
    // return $this->renderAbsy($block[1]);
  }

  protected function renderAbsy($blocks)
  {
    $output = '';
    foreach ($blocks as $block) {
      array_unshift($this->context, $block[0]);
      $r = $this->{'render' . $block[0]}($block);
      if ($this->rendering) $output .= $r;
      else if ($this->debugconditionals) $output .= '<del>' . $r . '</del>';
      array_shift($this->context);
    }
    return $output;
  }

}
