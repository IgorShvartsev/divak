<?php
namespace Builder;

/**
 * HtmlBuilder class
 * 
 * Example:
 * $enum = ['One', 'Two', 'Three', 'Four', 'Five'];
 * echo (new HtmlBuilder())->reset()
 *   ->setTabPointer(3)
 *   ->openTag('div', ['class' => 'myclass', 'id' => 'container', 'style' => 'margin:20px auto;max-width:600px'])
 *       ->openTag('form', ['id' => 'my-form', 'style' => 'width:100%', 'method' => 'POST'])
 *           ->startInline()
 *               ->openTag('label')->makeText('Username')->closeTag('label')
 *           ->endInline()
 *           ->makeInput('username', '', 'text', ['style' => 'padding:2 4px;width:100%'])
 *           ->startInline()
 *               ->openTag('textarea', ['style' => 'width:100%'])->makeText('Jump To FireFox')->closeTag('textarea')
 *           ->endInline()
 *           ->makeInput('submit', ' SUBMIT ', 'submit')
 *       ->closeTag('form')
 *       ->openTag('ul')
 *           ->makeCollection(function ($builder) use ($enum) {
 *               foreach ($enum as $txt) {
 *                   $builder->startInline()
 *                           ->openTag('li')
 *                           ->makeText($txt)
 *                           ->closeTag('li')
 *                           ->endInline();
 *               }
 *           })
 *       ->closeTag('ul')
 *   ->closeTag('div');
 *   ->build();
 *
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.2
 */
class HtmlBuilder
{
    /**
     * @var string
     */
    protected $html = '';

    /**
     * @var int
     */
    protected $tabCount = 0;

    /**
     * @var bool
     */
    protected $isEol = true;

    /**
     * @var bool
     */
    protected $isTabKey = true;

    /**
     * Set TAB pointer
     *
     * @param int $pointer
     * 
     * @return HtmlBuilder
     */
    public function setTabPointer($pointer)
    {
        $pointer = (int)$pointer;
        $this->tabCount = $pointer;

        return $this;
    }

    /**
     * Open tag
     *
     * @param string $tagName
     * @param bool $eol end of line output or not
     *
     * @return HtmlBuilder
     */
    public function openTag($tagName, array $attributes = [])
    {
        $el = $this->createTagElement($tagName, $attributes);

        if (!empty($el)) {
            $this->html .= $this->tabKey(true) . '<' . $el . '>' . ($this->isEol ? PHP_EOL : '');

            if ($this->isTabKey) {
                $this->tabCount++;
            }
        }

        return $this;
    }

    /**
     * Close tag
     *
     * @param string $tagName
     *
     * @return HtmlBuilder
     */
    public function closeTag($tagName)
    {
        if (!empty($tagName)) {
            if ($this->isTabKey) {
                $this->tabCount = $this->tabCount > 0 ? --$this->tabCount : 0;
            }

            $this->html .= $this->tabKey() . '</' . $tagName . '>' . ($this->isEol ? PHP_EOL : '');
        }

        return $this;
    }

    /**
     * Start inline elements
     * 
     * @return HtmlBuilder
     */
    public function startInline()
    {
        $this->isTabKey = false;
        $this->isEol = false;

        return $this;
    }

    /**
     * End inline elements
     * 
     * @return HtmlBuilder
     */
    public function endInline()
    {
        $this->isTabKey = true;
        $this->isEol = true;

        if (!empty($this->html)) {
            $this->html .= PHP_EOL;
        }

        return $this;
    }

    /**
     * Make input element
     *
     * @param string $name
     * @param string $value
     * @param string $type
     *
     * @return HtmlBuilder
     */
    public function makeInput($name, $value = '', $type = 'text', array $attributes = [])
    {
        $el = $this->tabKey() . '<input type="' . $this->escape($type)  . '" name="' . $this->escape($name) . '"';
        unset($attributes['name'], $attributes['type'], $attributes['value']);
        $el .= rtrim($this->attributesToString($attributes), ' ');
        $el .= ' value="' . htmlentities($value) . '" />';
        $this->html .= $el . ($this->isEol ? PHP_EOL : '');

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
        $this->html .= $this->tabKey() . htmlentities($text) . ($this->isEol ? PHP_EOL : '');

        return $this;
    }

    /**
     * Open tag
     *
     * @param string $name
     * @param string $selectedValue
     * @param array $attrubutes
     *
     * @return HtmlBuilder
     */
    public function makeSelect($name, array $optionList, $selectedValue = null, array $attributes = [])
    {
        $el = $this->tabKey() . '<select name="' . $this->escape($name) . '"';
        $el .= $this->attributesToString($attributes);
        $el .= '>' . ($this->isEol ? PHP_EOL : '');

        foreach ($optionList as $title => $value) {
            $selected = '';
            if (!empty($selectedValue) && $value == $selectedValue) {
                $selected = 'selected="selected"';
            }

            $el .= '<option value="' . $this->escape($value) . '" ' . $selected . '>'
                . htmlentities($title)
                . '</option>' . ($this->isEol ? PHP_EOL : '');
        }

        $el .= '</select>' . ($this->isEol ? PHP_EOL : '');

        $this->html .= $el;

        return $this;
    }

    /**
     * Make collection
     *
     * @return HtmlBuilder
     */
    public function makeCollection(callable $callback)
    {
        call_user_func_array($callback, [$this]);
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
        $this->tabCount = 0;
        $this->isTabKey = true;
        $this->isEol = true;

        return $this;
    }

    /**
     * Create Tag Element
     *
     * @param string $tagName
     *
     * @return string
     */
    protected function createTagElement($tagName, array $attributes = [])
    {
        $tagName = trim($tagName);
        $tagName = strtolower($tagName);
        $el = preg_replace('#[^A-Z0-9_-]#i', '', $tagName);

        if (!empty($el)) {
            $el .= $this->attributesToString($attributes);
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

    /**
     * Attributes to string
     *
     * @return string
     */
    protected function attributesToString(array $attributes = [])
    {
        $result = '';

        foreach ($attributes as $name => $val) {
            $name = preg_replace('#[^A-Z-]#i', '', $name);
            $result .= ' ' . $name . '="' . $this->escape($val) . '"';
        }

        return $result;
    }

    /**
     * Make tab key
     *
     * @return string
     */
    protected function tabKey($force = false)
    {
        if (($this->isTabKey || $force) && $this->tabCount > 0) {
            return str_repeat('    ', $this->tabCount);
        }

        return '';
    }
}
