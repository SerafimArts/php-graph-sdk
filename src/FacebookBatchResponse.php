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
namespace Facebook;

use ArrayIterator;
use IteratorAggregate;
use ArrayAccess;

/**
 * Class FacebookBatchResponse
 *
 * @package Facebook
 */
class FacebookBatchResponse extends FacebookResponse implements IteratorAggregate, ArrayAccess
{
    /**
     * @var array An array of FacebookResponse entities.
     */
    protected $responses = [];

    /**
     * Creates a new Response entity.
     */
    public function __construct(protected FacebookBatchRequest $batchRequest, FacebookResponse $response)
    {
        $request = $response->getRequest();
        $body = $response->getBody();
        $httpStatusCode = $response->getHttpStatusCode();
        $headers = $response->getHeaders();
        parent::__construct($request, $body, $httpStatusCode, $headers);

        $responses = $response->getDecodedBody();
        $this->setResponses($responses);
    }

    /**
     * Returns an array of FacebookResponse entities.
     *
     * @return array
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * The main batch response will be an array of requests so
     * we need to iterate over all the responses.
     */
    public function setResponses(array $responses)
    {
        $this->responses = [];

        foreach ($responses as $key => $graphResponse) {
            $this->addResponse($key, $graphResponse);
        }
    }

    /**
     * Add a response to the list.
     *
     * @param int        $key
     * @param array|null $response
     */
    public function addResponse($key, $response)
    {
        $originalRequestName = $this->batchRequest[$key]['name'] ?? $key;
        $originalRequest = $this->batchRequest[$key]['request'] ?? null;

        $httpResponseBody = $response['body'] ?? null;
        $httpResponseCode = $response['code'] ?? null;
        // @TODO With PHP 5.5 support, this becomes array_column($response['headers'], 'value', 'name')
        $httpResponseHeaders = isset($response['headers']) ? $this->normalizeBatchHeaders($response['headers']) : [];

        $this->responses[$originalRequestName] = new FacebookResponse(
            $originalRequest,
            $httpResponseBody,
            $httpResponseCode,
            $httpResponseHeaders
        );
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): \Traversable
    {
        return new ArrayIterator($this->responses);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->addResponse($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->responses[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->responses[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->responses[$offset] ?? null;
    }

    /**
     * Converts the batch header array into a standard format.
     * @TODO replace with array_column() when PHP 5.5 is supported.
     *
     *
     * @return array
     */
    private function normalizeBatchHeaders(array $batchHeaders)
    {
        $headers = [];

        foreach ($batchHeaders as $header) {
            $headers[$header['name']] = $header['value'];
        }

        return $headers;
    }
}
