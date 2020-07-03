<?php
/**
 * ResponseJson
 *
 * This class holds the information to be sent as feedback to the user
 *
 * @package    Freezer
 * @author     Lawrence Lagerlof <llagerlof@gmail.com>
 * @copyright  2020 Lawrence Lagerlof
 * @link       http://github.com/llagerlof/freezer
 * @license    https://opensource.org/licenses/MIT MIT
 */
class ResponseJson
{
    /**
     * The messages
     *
     * @var array
     */
    private $messages = array();

    /**
     * The errors
     *
     * @var array
     */
    private $errors = array();

    /**
     * The new records
     *
     * @var array
     */
    private $diff = array();

    /**
     * Get the messages
     *
     * @return array The messages
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Get the errors
     *
     * @return array The errors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get the new records
     *
     * @return array The new records
     */
    public function getDiff()
    {
        return $this->diff;
    }

    /**
     * Set the messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    /**
     * Set a message
     */
    public function setMessage($message)
    {
        $this->messages[] = $message;
    }

    /**
     * Set the errors
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    /**
     * Set a error
     */
    public function setError($error)
    {
        $this->errors[] = $error;
    }

    /**
     * Set the new records
     */
    public function setDiff($diff)
    {
        $this->diff = $diff;
    }

    /**
     * Get the response as will be sent to the user's ajax request
     *
     * @return array The response to the user
     */
    public function getResponse($action)
    {
        $response = new StdClass();
        $response->messages = $this->getMessages();
        $response->errors = $this->getErrors();
        if ($action == Freezer::DIFF) {
            $response->diff = $this->getDiff();
        }

        return $response;
    }
}
