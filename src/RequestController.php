<?php
namespace AlexNzr\BitUmcIntegration;

class RequestController{

    protected function __construct(){}

    /** checks the name of action and calls the relevant service
     * @param array $data
     * @return string
     */
    public static function sendRequest(array $data): string
    {
        $data = Utils::cleanRequestData($data);
        if (!empty($data['action']))
        {
            $action = $data['action'];

            switch ($action) {
                case 'GetListClients':
                    $response = RequestService::getListClients();
                    break;
                case 'GetListClinics':
                    $response = RequestService::getListClinics();
                    break;
                case 'GetListEmployees':
                    $response = RequestService::getListEmployees();
                    break;
                case 'GetSchedule':
                    $response = RequestService::getSchedule();
                    break;
                case 'CreateOrder':
                    $response = RequestService::createOrder($data);
                    break;
                default:
                    $response = Utils::addError('Unknown action - '.$action);
                    break;
            }
            return $response;
        }
        else
        {
            return Utils::addError('Action is empty');
        }
    }
}
