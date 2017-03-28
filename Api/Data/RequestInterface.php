<?php
/**
 * @package Guapa_Paazl
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
namespace Guapa\Paazl\Api\Data;

interface RequestInterface
{
    const RESPONSE = 'response';
    const ERRORS = 'errors';
    
    /**
     * @return array
     */
    public function getClientConfig();

    /**
     * @return string|int
     */
    public function getMethod();

    /**
     * @return array
     */
    public function getHeaders();
    
    /**
     * @return array|string
     */
    public function getBody();

    /**
     * @return array|string
     */
    public function getAuth();
    
    /**
     * @return mixed
     */
    public function getResponse();

    /**
     * @param array $response
     * @return mixed
     */
    public function setResponse($response);

    /**
     * @return mixed
     */
    public function getErrors();

    /**
     * @param array $errors
     * @return mixed
     */
    public function setErrors($errors);
}
