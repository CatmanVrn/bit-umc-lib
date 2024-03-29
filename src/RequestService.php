<?php
namespace AlexNzr\BitUmcIntegration;

use Exception;

class RequestService{

    private static string $baseurl = Variables::PROTOCOL . Variables::COLON . Variables::D_SEP .
    Variables::BASE_ADDR . Variables::SEP .
    Variables::BASE_NAME . Variables::SEP .
    Variables::HTTP_SERVICE_PREFIX . Variables::SEP .
    Variables::HTTP_SERVICE_NAME . Variables::SEP .
    Variables::HTTP_SERVICE_API_VERSION . Variables::SEP;

    protected function __construct(){}

    /** get list of clients in json
     * @return string
     */
    public static function getListClients(): string
    {
        $data = json_decode(self::post("GetListClients"), true);
        $clients = $data["clients"];
        if (empty($data["error"]) && is_array($clients))
        {
            foreach ($clients as $key => $client)
            {
                if (!empty($client["birthday"]))
                {
                    $clients[$key]["displayBirthday"] = date("d-m-Y", strtotime($client["birthday"]));
                }

                if (is_array($client["contacts"]))
                {
                    foreach($client['contacts'] as $contactType => $contactValue){
                        if ($contactType === "phone")
                        {
                            $clients[$key]["contacts"]["phone"] = Utils::formatPhone($contactValue);
                        }
                        else
                        {
                            $clients[$key]["contacts"][$contactType] = trim($contactValue);
                        }
                    }
                }

                foreach ($clients[$key] as $param => $value){
                    if (is_string($value)){
                        $clients[$key][$param] = trim($value);
                    }
                }
            }
            $data = $clients;
        }

        return json_encode($data);
    }

    /** get list of orders in json
     * @param array $params
     * @return string
     */
    public static function getListOrders(array $params): string
    {
        if (!empty($params["clientUid"])){
            $data = json_decode(self::post("GetListOrders", $params), true);
            $orders = $data["orders"];
            if (empty($data["error"]) && is_array($orders))
            {
                foreach ($orders as $key => $order)
                {
                    if (!empty($order["orderDate"])){
                        $orders[$key]["displayOrderDate"] = date("d-m-Y", strtotime($order["orderDate"]));
                    }
                    if (!empty($order["timeBegin"])){
                        $orders[$key]["displayTimeBegin"] = date("H:i", strtotime($order["timeBegin"]));
                    }
                    if (!empty($order["timeEnd"])){
                        $orders[$key]["displayTimeEnd"] = date("H:i", strtotime($order["timeEnd"]));
                    }
                    if (!empty($order["clientBirthday"])){
                        $orders[$key]["displayClientBirthday"] = date("d-m-Y", strtotime($order["clientBirthday"]));
                    }

                    $data = $orders;
                }
            }

            return json_encode($data);
        }
        return Utils::addError('ClientUid is empty');
    }

    /** get list of clinics in json
     * @return string
     */
    public static function getListClinics(): string
    {
        $data = json_decode(self::post("GetListClinics"), true);
        $clinics = $data["clinics"];
        if (empty($data["error"]) && is_array($clinics))
        {
            foreach ($clinics as $key => $clinic)
            {
                $clinics[$key]["uid"] = trim($clinic["uid"]);
                $clinics[$key]["name"] = trim($clinic["name"]);
            }
            $data = $clinics;
        }
        return json_encode($data);
    }

    /** get list of employees in json
     * @param $params
     * @return string
     */
    public static function getListEmployees($params): string
    {
        $data = json_decode(self::post("GetListEmployees", $params), true);
        $employees = $data["employees"];
        if (empty($data["error"]) && is_array($employees))
        {
            $data = $employees;
        }
        return json_encode($data);
    }

    /** get list of nomenclature in json
     * @param $params
     * @return string
     */
    public static function getListNomenclature($params): string
    {
        $data = json_decode(self::post("GetListNomenclature", $params), true);
        $nomenclature = $data["nomenclature"];
        if (empty($nomenclature["error"]) && is_array($nomenclature))
        {
            $data = $nomenclature;
        }
        return json_encode($data);
    }

    /** get doctor's or cabinet's schedule in json
     * @return string
     */
    public static function getSchedule(): string
    {
        $period = Utils::getDateInterval(Variables::SCHEDULE_PERIOD_IN_DAYS);

        $data = json_decode(self::post('GetSchedule', $period), true);
        $schedule = $data["ГрафикиДляСайта"]["ГрафикДляСайта"];
        if (!empty($schedule) && empty($schedule["error"]))
        {
            if (is_array($schedule)){
                if (Utils::is_assoc($schedule))
                {
                    $schedule = array($schedule);
                }

                $formattedSchedule = [];
                foreach ($schedule as $key => $item)
                {
                    if (isset($item["СотрудникID"])){
                        $formattedSchedule[$key]["refUid"] = $item["СотрудникID"];
                    }
                    if (isset($item["Специализация"])){
                        $formattedSchedule[$key]["specialty"] = $item["Специализация"];
                    }
                    if (isset($item["СотрудникФИО"])){
                        $formattedSchedule[$key]["name"] = $item["СотрудникФИО"];
                    }
                    if (isset($item["Клиника"])){
                        $formattedSchedule[$key]["clinicUid"] = $item["Клиника"];
                    }

                    $duration = 0;
                    if (isset($item["ДлительностьПриема"])){
                        $formattedSchedule[$key]["duration"] = $item["ДлительностьПриема"];
                        $duration = intval(date("H", strtotime($item["ДлительностьПриема"]))) * 3600
                            + intval(date("i", strtotime($item["ДлительностьПриема"]))) * 60;
                        $formattedSchedule[$key]["durationInSeconds"] = $duration;
                    }

                    $freeTime = is_array($item["ПериодыГрафика"]["СвободноеВремя"])
                        ? $item["ПериодыГрафика"]["СвободноеВремя"]["ПериодГрафика"] : [];
                    $busyTime = is_array($item["ПериодыГрафика"]["ЗанятоеВремя"])
                        ? $item["ПериодыГрафика"]["ЗанятоеВремя"]["ПериодГрафика"] : [];
                    if (Utils::is_assoc($freeTime)) {
                        $freeTime = array($freeTime);
                    }
                    if (Utils::is_assoc($busyTime)) {
                        $busyTime = array($busyTime);
                    }

                    $formattedSchedule[$key]["timetable"]["free"] = Utils::formatTimetable($freeTime, $duration);
                    $formattedSchedule[$key]["timetable"]["busy"] = Utils::formatTimetable($busyTime, 0, true);
                    $formattedSchedule[$key]["timetable"]["freeNotFormatted"] = Utils::formatTimetable($freeTime, 0, true);
                }
                $data = [
                    "schedule" => $formattedSchedule,
                ];
            }
        }
        return json_encode($data);
    }

    /** make request to creating order
     * @param array $params
     * @param bool $unauthorized
     * @return string
     */
    public static function createOrder(array $params, bool $unauthorized = false): string
    {
        if (Utils::validateOrderParams($params))
        {
            $params['orderDate'] = Utils::formatDateToOrder($params['orderDate']);
            $params['timeBegin'] = Utils::formatDateToOrder($params['timeBegin'], true);
            $params['timeEnd'] = Utils::formatDateToOrder($params['timeEnd'], true);

            if ($unauthorized)
            {
                $params["comment"] =    $params['name'] . " "
                    . $params['middleName'] . " "
                    . $params['surname'] . "\n"
                    . $params['phone'] ."\n". $params["comment"];

                $params['unauthorized'] = "Y";
            }

            return self::post('CreateOrder', $params);
        }
        return Utils::addError('Not enough params to make appointment');
    }

    /** cancelling order in 1C
     * @param array $params
     * @return string
     */
    public static function cancelOrder(array $params): string
    {
        if (!empty($params["orderUid"])){
            return self::post('CancelOrder', $params);
        }
        return Utils::addError('orderUid is empty');
    }

    /** make request to update client's data
     * @param array $params
     * @return string
     */
    public static function updateClient(array $params): string
    {
        if (!empty($params["clientUid"]))
        {
            foreach ($params as $key => $value) {
                switch ($key){
                    case "name":
                    case "surname":
                    case "middlename":
                    case "emailHome":
                    case "emailWork":
                        $params[$key] = trim($value);
                        break;
                    case "phone":
                        $params["phone"] = Utils::formatPhone($value);
                        break;
                }
            }
            return self::post('UpdateClient', $params);
        }
        return Utils::addError('"clientUid" is necessary to update client data');
    }

    /** send request to 1C database
     * @param string $method
     * @param array $params
     * @return string
     */
    protected static function post(string $method, array $params = []): string
    {
        $requestUrl = self::$baseurl . $method;

        if($curl = curl_init())
        {
            $authToken = base64_encode(Variables::AUTH_LOGIN_1C.Variables::COLON.Variables::AUTH_PASSWORD_1C);
            $headers = array(
                "Accept: application/json",
                "Authorization: Basic " . $authToken,
                "Content-Type: application/json;charset=utf-8",
            );

            $postData = json_encode($params);

            try
            {
                curl_setopt($curl, CURLOPT_URL, $requestUrl);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                $response = curl_exec($curl);
                $error = curl_error($curl);
                curl_close($curl);

                if (self::isJSON($response)) {
                    return $response;
                }
                else{
                    if ($error){
                        return Utils::addError("Connection error - " . $error);
                    }
                    return Utils::addError("Wrong response - " . json_encode($response));
                }
            }
            catch (Exception $e)
            {
                return Utils::addError($e->getMessage());
            }
        }else{
            return Utils::addError('Connection init error');
        }
    }

    /** checking the validity of the json
     * @param $string
     * @return bool
     */
    private static function isJSON($string): bool
    {
        return is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string, true)));
    }
}
