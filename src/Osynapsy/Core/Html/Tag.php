<?php
/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Core\Html;

class Tag
{
    const TAG_WITHOUT_CLOSURE = ['input','img','link','meta'];

    private $attributes = [];
    private $childs = [];
    public $ref = [];
    public $parent = null;

    /**
     * Constructor of tag
     *
     * @param type $tag to build
     * @param type $id identity of tag
     * @param type $class css class
     */
    public function __construct($tag = 'dummy', $id = null, $class = null)
    {
        $this->att(0, $tag);
        if (!empty($id)) {
            $this->att('id', str_replace(['[',']'], ['-',''], $id));
        }
        if (!empty($class)) {
            $this->att('class', $class);
        }
    }

    /**
     * Check if inaccessible property is in attribute
     *
     * @param type $attribute
     * @return type
     */
    public function __get($attribute)
    {
        if ($attribute == 'tag') {
            return $this->attributes[0];
        }
        return array_key_exists($attribute, $this->attributes) ? $this->attributes[$attribute] : null;
    }

    /**
     *
     * @param type $attribute
     * @param type $value
     */
    public function __set($attribute, $value)
    {
        if (is_array($value)) {
            throw \Exception('Illegal content of value attribute' . print_r($value, true));
        }
        $this->attributes[$attribute] = $value;
    }

    /**
     * Add child content to childs repo
     *
     * @param $child
     * @return \Osynapsy\Html\tag|$this
     */
    public function add($child)
    {
        if ($child instanceof tag) {
            if ($child->id && array_key_exists($child->id,$this->ref)) {
                return $this->ref[$child->id];
            }
        }
        //Append child to childs repo
        $this->childs[] = $child;
        //If child isn't object return $this tag
        if (!is_object($child)) {
            return $this;
        }
        if ($child->id) {
            $this->ref[$child->id] =& $child;
        }
        $child->parent =& $this;
        return $child;
    }

    public function addClass($class)
    {
        return empty($class) ? $this : $this->att('class', $class, true);
    }

    /**
     * Add childs from array
     *
     * @param array $array
     * @return $this
     */
    public function addFromArray(array $array)
    {
        foreach ($array as $child) {
            $this->add($child);
        }
        return $this;
    }

    /**
     * Set attribute value of tag
     *
     * @param type $attribute
     * @param type $value
     * @param type $concat
     * @return $this
     */
    public function att($attribute, $value = '', $concat = false)
    {
        if (is_array($attribute)) {
            foreach ($attribute as $key => $value) {
                $this->attributes[$key] = $value;
            }
        } elseif ($concat && !empty($this->attributes[$attribute])) {
            $this->attributes[$attribute] .= ($concat === true ? ' ' : $concat) . $value;
        } else {
            $this->attributes[$attribute] = $value;
        }
        return $this;
    }

    /**
     * Build html tag e return string
     *
     * @return string
     */
    protected function build($depth = 0)
    {
        $tag = array_shift($this->attributes);
        if ($tag === 'dummy') {
            return $this->buildContentTag($depth - 1, '');
        }
        $indentation = $this->buildIndedation($depth);
        $result = $indentation . $this->buildOpeningTag($tag);
        if (!in_array($tag, self::TAG_WITHOUT_CLOSURE)) {
            $result .= $this->buildContentTag($depth);
            $result .= $this->indendationBeforeClosingTag($indentation);
            $result .= $this->buildClosingTag($tag);
        }
        return $result.PHP_EOL;
    }

    protected function buildIndedation($depth)
    {
        return $depth > 0 ? str_repeat("\t", $depth) : '';
    }

    protected function buildOpeningTag($tag)
    {
        $attributes = '';
        foreach ($this->attributes as $attribute => $value) {
            if (is_object($value) && !method_exists($value, '__toString')) {
                $attributes .= ' error="Attribute value is object ('.get_class($value).')"';
                continue;
            } elseif (is_array($value)) {
                $attributes .= ' error="Attribute value is array"';
                continue;
            }
            $attributes .= sprintf(' %s="%s"', $attribute, htmlspecialchars($value, ENT_QUOTES));
        }
        return sprintf('<%s%s>' , $tag, $attributes);
    }

    protected function buildContentTag($depth, $carriageReturn = PHP_EOL)
    {
        $result = '';
        foreach ($this->childs as $i => $content) {
            if ($i === 0 && $content instanceof tag) {
                $result .= $carriageReturn;
            }
            $result .= $content instanceof tag ? $content->build($depth + 1) : $content;
        }
        return empty($result) ? '' : $result;
    }

    protected function indendationBeforeClosingTag($indentation)
    {
        return (!empty($this->childs) && $this->childs[0] instanceof tag) ? $indentation : '';
    }

    protected function buildClosingTag($tag)
    {
        return  "</{$tag}>";
    }

    /**
     * Static method for create a tag object
     *
     * @param string $tag
     * @param string $id
     * @return \Osynapsy\Html\tag
     */
    public static function create($tag, $id = null, $class = null)
    {
        return new Tag($tag, $id, $class);
    }

    /**
     * Get html string of tag
     *
     * @return type
     */
    public function get()
    {
        return $this->build();
    }

    public function getAttribute($attributeId)
    {
        return array_key_exists($attributeId, $this->attributes) ? $this->attributes[$attributeId] : null;
    }

    /**
     * Get $index child from repo
     *
     * @param int $index
     * @return boolean
     */
    public function child($index = 0)
    {
        if (is_null($index)) {
            return $this->childs;
        }
        if (array_key_exists($index, $this->childs)) {
            return $this->childs[$index];
        }
        return false;
    }

    /**
     * Check if tag content is empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return count($this->childs) > 0 ? false : true;
    }

    /**
     * Magic method for rendering tag in html
     *
     * @return type
     */
    public function __toString()
    {
        return $this->get();
    }
}
