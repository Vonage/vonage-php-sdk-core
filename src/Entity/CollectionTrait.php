<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Entity;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Request;
use Nexmo\Application\Application;

trait CollectionTrait
{
    /**
     * Index of the current resource of the current page
     * @var int
     */
    protected $current;

    /**
     * Current page data.
     * @var array
     */
    protected $page;

    /**
     * Last API Response
     * @var ResponseInterface
     */
    protected $response;

    /**
     * User set page index.
     * @var int
     */
    protected $index;

    /**
     * User set pgge sixe.
     * @var int
     */
    protected $size;

    /**
     * @var FilterInterface
     */
    protected $filter;

    abstract public function getCollectionName();
    abstract public function getCollectionPath();

    public function current()
    {
        $application = new Application($this->key());
        $application->jsonUnserialize($this->page['_embedded'][$this->getCollectionName()][$this->current]);
        return $application;
    }

    public function next()
    {
        $this->current++;
    }

    public function key()
    {
        return $this->page['_embedded'][$this->getCollectionName()][$this->current]['id'];
    }

    public function valid()
    {
        if(!isset($this->page)){
            return false;
        }

        if(!isset($this->page['_embedded']) OR !isset($this->page['_embedded'][$this->getCollectionName()])){
            return false;
        }

        if(!count($this->page['_embedded'][$this->getCollectionName()])){
            return false;
        }

        if(is_null($this->current)){
            $this->current = 0;
        }

        if(!isset($this->page['_embedded'][$this->getCollectionName()][$this->current])){
            if(isset($this->page['_links']) AND isset($this->page['_links']['next'])){
                $this->fetchPage($this->page['_links']['next']['href']);
                $this->current = 0;

                return true;
            }

            return false;
        }

        return true;
    }

    public function rewind()
    {
        $this->fetchPage($this->getCollectionPath());
    }

    public function count()
    {
        if(isset($this->page)){
            return $this->page['count'];
        }
    }

    public function setPage($index)
    {
        $this->index = (int) $index;
        return $this;
    }

    public function getPage()
    {
        if(isset($this->page)){
            return $this->page['page_index'];
        }

        if(isset($this->index)){
            return $this->index;
        }

        throw new \RuntimeException('page not set');
    }

    public function getSize()
    {
        if(isset($this->page)){
            return $this->page['page_size'];
        }

        if(isset($this->size)){
            return $this->size;
        }

        throw new \RuntimeException('size not set');
    }

    public function setSize($size)
    {
        $this->size = (int) $size;
        return $this;
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function setFilter(FilterInterface $filter)
    {
        $this->filter = $filter;
        return $this;
    }

    public function getFilter()
    {
        if(!isset($this->filter)){
            $this->setFilter(new EmptyFilter());
        }

        return $this->filter;
    }

    protected function fetchPage($absoluteUri)
    {
        if(false === strpos($absoluteUri, '?')){
            $query = [];

            if(isset($this->size)){
                $query['page_size'] = $this->size;
            }

            if(isset($this->index)){
                $query['page_index'] = $this->index;
            }

            if(isset($this->filter)){
                $query = array_merge($this->filter->getQuery(), $query);
            }

            $absoluteUri .= '?' . http_build_query($query);
        }

        $request = new Request(
            \Nexmo\Client::BASE_API . $absoluteUri,
            'GET'
        );

        $response = $this->client->send($request);

        if($response->getStatusCode() != '200'){
            throw $this->getException($response);
        }

        $this->response = $response;
        $this->page = json_decode($this->response->getBody()->getContents(), true);
    }
}