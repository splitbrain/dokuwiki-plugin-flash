<?php
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');


/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_flash extends DokuWiki_Syntax_Plugin {

    /**
     * return some info
     */
    function getInfo(){
        return confTohash(dirname(__FILE__).'/plugin.info.txt');
    }

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    function getPType(){
        return 'block';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 160;
    }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
      $this->Lexer->addSpecialPattern('<flash.*?>\n.*?\n</flash>',$mode,'plugin_flash');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler){

        // prepare default data
        $return = array(
                     'data'   => $data,
                     'width'  => 425,
                     'height' => 350,
                     'align'  => 'center',
                    );

        // prepare input
        $lines = explode("\n",$match);
        $conf = array_shift($lines);
        $conf = substr($conf,6,-1);
        array_pop($lines);

        // parse adhoc configs
        if(preg_match('/\b(left|center|right)\b/i',$conf,$match)) $return['align'] = $match[1];
        if(preg_match('/\b(\d+)x(\d+)\b/',$conf,$match)){
            $return['width']  = $match[1];
            $return['height'] = $match[2];
        }

        // strip configs to find swf
        $conf = preg_replace('/\b(left|center|right|(\d+)x(\d+))\b/i','',$conf);
        $conf = trim($conf);
        $return['swf'] = ml($conf,'',true,'&');

        // parse parameters
        $return['data'] = linesToHash($lines);
        foreach($return['data'] as $key => $val){
            if($key{0} == '!') {
                $return['data'][substr($key,1)] = ml($val,'',true,'&');
                unset($return['data'][$key]);
            }
        }

        return $return;
    }

    /**
     * Create output
     */
    function render($mode, Doku_Renderer $R, $data) {
        if($mode != 'xhtml') return false;

        $att = array();
        $att['class'] = 'media'.$data['align'];
        if($data['align'] == 'right') $att['align'] = 'right';
        if($data['align'] == 'left')  $att['align'] = 'left';

        $R->doc .= html_flashobject($data['swf'],$data['width'],$data['height'],$data['data'],$data['data'],$att);

        return true;
    }


}

//Setup VIM: ex: et ts=4 enc=utf-8 :
