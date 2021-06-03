<?php
namespace Builder;

/**
 * HtmlBuilder class
 *
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.1
 */
class HtmlBuilder
{
    /** 
     * @var string $html
     *
     */
    protected $html = '';

    /**
     * Open tag 
     *
     * @param string $tagName
     * @param string $class
     * @param string $id
     *
     * @return HtmlBuilder
     */
    public function openTag($tagName, $class = null, $id = null, $style = null)
    {
        $el = $this->createTagElement($tagName, $class, $id, $style);

        if (!empty($el)) {
            $this->html .= '<' . $el . '>' . PHP_EOL; 
        }

        return $this;
    }

    /**
     * Close tag 
     *
     * @param string $tagName
     * @param string $class
     * @param string $id
     *
     * @return HtmlBuilder
     */
    public function closeTag($tagName, $class = null, $id = null, $style = null)
    {
        $el = $this->createTagElement($tagName, $class, $id, $style);

        if (!empty($el)) {
            $this->html .= '</' . $el . '>' . PHP_EOL; 
        }

        return $this;
    }

    /**
     * Make input text element 
     *
     * @param string $name
     * @param string $value
     * @param string $class
     * @param string $id
     * @param string $style
     *
     * @return HtmlBuilder
     */
    public function makeTextInput($name, $value = '', $class = null, $id = null, $style = null)
    {
        $el = '<input type="text" name="' . $this->escape($name) . '" ';

        if (!empty($class)) {
            $el .= 'class="' . $this->escape($class) . '" ';
        }

        if (!empty($id)) {
            $el .= 'id="' . $this->escape($id) . '" ';
        }

        if (!empty($style)) {
            $el .= 'style="' . $this->escape($style) . '" ';
        }

        $el .= 'value="' . htmlentities($value) . '" />';
        $this->html .= $el;

        return $this;
    }

    /**
     * Make text
     *
     * @param string $text 
     *
     * @return HtmlBuilder  
     */
    public function makeText($text)
    {
        $this->html .= htmlentities($text);

        return $this;
    }

    /**
     * Open tag 
     *
     * @param array $optionList
     * @param string $name
     * @param string $selectedValue
     * @param string $class
     * @param string $id
     * @param string $style
     *
     * @return HtmlBuilder
     */
    public function makeSelect(
        array $optionList, 
        $name, 
        $selectedValue = null,
        $class = null, 
        $id = null,
        $style = null 
    ) {
        $el = '<select name="' . $this->escape($name) . '" ';
        
        if (!empty($class)) {
            $el .= 'class="' . $this->escape($class) . '" ';
        }

        if (!empty($id)) {
            $el .= 'id="' . $this->escape($id) . '" ';
        }

        if (!empty($style)) {
            $el .= 'style="' . $this->escape($style) . '" ';
        }

        $el .= '>' . PHP_EOL;

        foreach ($optionList as $title => $value) {
            $selected = '';
            if (!empty($selectedValue) && $value == $selectedValue) {
                $selected = 'selected = "selected"';
            } 

            $el .= '<option value="' . $this->escape($value) . '" ' . $selected . '>' 
                . htmlentities($title) 
                . "</option>" . PHP_EOL;
        }

        $el .= '</select>' . PHP_EOL;

        $this->html .= $el;

        return $this; 
    }

    /**
     * Build and output result 
     */
    public function build()
    {
        return $this->html;
    }

    /**
     * Reset output (clean html buffer)
     *
     * @return HtmlBuilder
     */
    public function reset()
    {
        $this->html = '';

        return $this;
    }

    /**
     * Create Tag Element
     *
     * @param string $tagName
     * @param string $class
     * @param string $id
     * @param string $style
     *
     * @return string
     */
    protected function createTagElement($tagName, $class = null, $id = null, $style = null)
    {
        $tagName = trim($tagName);
        $tagName = strtolower($tagName);
        $el = preg_replace('#[^A-Z0-9_:<>-]#i', '', $tagName);

        if (!empty($el)) {
            if (!empty($class)) {
                $el .= ' class="' . $this->escape($class) . '" ';
            }

            if (!empty($id)) {
                $el .= ' id="' . $this->escape($id) . '" ';
            }

            if (!empty($style)) {
                $el .= ' style="' . $this->escape($style) . '" ';
            }   
        }

        return trim($el);
    }

    /**
     * Escape value
     *
     * @param string $value
     *
     * @return string
     */
    protected function escape($value)
    {
        if (is_string($value)) {
            $result = preg_replace(['#\"#'], ['`'], $value);
        } elseif (is_bool($value)) {
            if (true === $value) {
                $result = 'true';
            } else {
                $result = 'false';
            }
        } elseif (is_array($value)) {
            $result = '[array]';
        } elseif (is_object($value)) {
            $result = '{object}';
        } else {
            $result = $value;
        }

        return $result;
    }
}
