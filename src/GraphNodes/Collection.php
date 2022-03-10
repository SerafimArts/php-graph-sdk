<?php
/**
 * Copyright 2017 Facebook, Inc.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */
namespace Facebook\GraphNodes;

/**
 * Class Collection
 *
 * Modified version of Collection in "illuminate/support" by Taylor Otwell
 *
 * @package Facebook
 */

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

class Collection implements ArrayAccess, Countable, IteratorAggregate, \Stringable
{
    /**
     * Create a new collection.
     */
    public function __construct(protected array $items = [])
    {
    }

    /**
     * Gets the value of a field from the Graph node.
     *
     * @param string $name    The field to retrieve.
     * @param mixed  $default The default to return if the field doesn't exist.
     *
     * @return mixed
     */
    public function getField($name, $default = null)
    {
        if (isset($this->items[$name])) {
            return $this->items[$name];
        }

        return $default;
    }

    /**
     * Gets the value of the named property for this graph object.
     *
     * @param string $name    The property to retrieve.
     * @param mixed  $default The default to return if the property doesn't exist.
     *
     * @return mixed
     *
     * @deprecated 5.0.0 getProperty() has been renamed to getField()
     * @todo v6: Remove this method
     */
    public function getProperty($name, $default = null)
    {
        return $this->getField($name, $default);
    }

    /**
     * Returns a list of all fields set on the object.
     *
     * @return array
     */
    public function getFieldNames()
    {
        return array_keys($this->items);
    }

    /**
     * Returns a list of all properties set on the object.
     *
     * @return array
     *
     * @deprecated 5.0.0 getPropertyNames() has been renamed to getFieldNames()
     * @todo v6: Remove this method
     */
    public function getPropertyNames()
    {
        return $this->getFieldNames();
    }

    /**
     * Get all of the items in the collection.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function asArray()
    {
        return array_map(fn($value) => $value instanceof Collection ? $value->asArray() : $value, $this->items);
    }

    /**
     * Run a map over each of the items.
     *
     *
     * @return static
     */
    public function map(\Closure $callback)
    {
        return new static(array_map($callback, $this->items, array_keys($this->items)));
    }

    /**
     * Get the collection of items as JSON.
     *
     * @param int $options
     *
     * @return string
     */
    public function asJson($options = 0)
    {
        return json_encode($this->asArray(), $options | \JSON_THROW_ON_ERROR);
    }

    /**
     * Count the number of items in the collection.
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator
     */
    public function getIterator(): \Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Determine if an item exists at an offset.
     *
     *
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * Get an item at a given offset.
     *
     *
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    /**
     * Set the item at a given offset.
     *
     *
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param string $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * Convert the collection to its string representation.
     */
    public function __toString(): string
    {
        return $this->asJson();
    }
}
