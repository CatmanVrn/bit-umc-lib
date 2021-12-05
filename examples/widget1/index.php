<?php

if ( !is_file(realpath(__DIR__ . '/../../vendor/autoload.php')) 
    && !is_file(realpath(__DIR__ . '/../../../../../vendor/autoload.php')))
{
    echo '<script>console.error("Autoloader not found");</script>';
}
else
{
    require_once __DIR__ . '/../../vendor/autoload.php';
    \AlexNzr\BitUmcIntegration\Utils::print(
            json_decode(\AlexNzr\BitUmcIntegration\RequestController::sendRequest(json_encode([
                    //"action"=>"GetListNomenclature",
                    //"employeeUid" => "ac30e139-3087-11dc-8594-005056c00008"
            ])), true)
    );

    $ajaxPath = substr(realpath(__DIR__.'/ajax/ajax.php'), strlen($_SERVER['DOCUMENT_ROOT']));
    $ajaxPath = explode(DIRECTORY_SEPARATOR, $ajaxPath);
    $ajaxPath = implode("/", $ajaxPath);
    $ajaxPath = $ajaxPath[0] === "/" ? $ajaxPath : "/" . $ajaxPath;

    $styleRealPath = realpath(__DIR__.'/assets/css/style.css');
    $styleHref = substr($styleRealPath, strlen($_SERVER['DOCUMENT_ROOT']));
    $styleHref = $styleHref[0] === DIRECTORY_SEPARATOR ? $styleHref : DIRECTORY_SEPARATOR . $styleHref;

    $scriptRealPath = realpath(__DIR__.'/assets/js/script.js');
    $scriptSrc = substr($scriptRealPath, strlen($_SERVER['DOCUMENT_ROOT']));
    $scriptSrc = $scriptSrc[0] === DIRECTORY_SEPARATOR ? $scriptSrc : DIRECTORY_SEPARATOR . $scriptSrc;

    $wrapperId = "appointment-widget-wrapper";
    $widgetBtnWrapId = "appointment-button-wrapper";
    $widgetBtnId = "appointment-button";
    $formId = 'appointment-form';
    $messageNodeId = 'appointment-form-message';
    $submitBtnId = "appointment-form-button";
    $appResultBlockId = "appointment-result-block";

    $nameInputId = "appointment-form-name";
    $middleNameInputId = "appointment-form-middleName";
    $surnameInputId = "appointment-form-surname";
    $phoneInputId = "appointment-form-phone";
    $commentInputId = "appointment-form-comment";

    $clinicsKey = "FILIAL";
    $specialtiesKey = "SPECIALTY";
    $servicesKey = "SERVICE";
    $employeesKey = "DOCTOR";
    $scheduleKey = "DATE_TIME";

    $blocksInfo = [
        $clinicsKey => "Выберите клинику",
        $specialtiesKey => "Выберите специализацию",
        $servicesKey => "Выберите услугу",
        $employeesKey => "Выберите врача",
        $scheduleKey => "Выберите время"
    ];
?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="<?=$styleHref?>?<?=filemtime($styleRealPath)?>">
<script src="<?=$scriptSrc?>?<?=filemtime($scriptRealPath)?>"></script>

<div class="widget-wrapper" id="<?=$wrapperId?>">
    <div class="appointment-button-wrapper loading" id="<?=$widgetBtnWrapId?>">
        <button id="<?=$widgetBtnId?>"></button>
        <div class="appointment-loader">
            <?for ($i = 1; $i <= 5; $i++ ):?>
                <div class="wBall" id="wBall_<?=$i?>"><div class="wInnerBall"></div></div>
            <?endfor;?>
        </div>
    </div>

    <form id="<?=$formId?>" class="appointment-form">
        <?foreach($blocksInfo as $key => $text):?>
            <div class="selection-block <?=($key !== $clinicsKey ? 'hidden' : '')?>" id="<?=$key?>_block">
                <p class="selection-item-selected" id="<?=$key?>_selected"><?=$text?></p>
                <ul class="appointment-form_head_list selection-item-list" id="<?=$key?>_list"></ul>
                <input type="hidden" name="<?=$key?>" id="<?=$key?>_value">
            </div>
        <?endforeach;?>

        <label class="appointment-form_input-wrapper">
            <input type="text" class="appointment-form_input" placeholder="Имя *" id="<?=$nameInputId?>" maxlength="30">
        </label>

        <label class="appointment-form_input-wrapper">
            <input type="text" class="appointment-form_input" placeholder="Отчество *" id="<?=$middleNameInputId?>" maxlength="30">
        </label>

        <label class="appointment-form_input-wrapper">
            <input type="text" class="appointment-form_input" placeholder="Фамилия *" id="<?=$surnameInputId?>" maxlength="30">
        </label>

        <label class="appointment-form_input-wrapper">
            <input type="tel" class="appointment-form_input" placeholder="Телефон *" id="<?=$phoneInputId?>" autocomplete="new-password" aria-autocomplete="list">
        </label>

        <label class="appointment-form_input-wrapper">
            <textarea class="appointment-form_textarea" placeholder="Комментарий" id="<?=$commentInputId?>" maxlength="300"></textarea>
        </label>

        <p id="<?=$messageNodeId?>"></p>

        <div class="appointment-form_submit-wrapper">
            <button type="submit" id="<?=$submitBtnId?>" class="appointment-form_button">Записаться на приём</button>
        </div>

        <div id="<?=$appResultBlockId?>"><p></p></div>
    </form>
</div>
    <script>
        document.addEventListener('DOMContentLoaded', ()=>{
            window.appointmentWidget.init({
                "useServices": "Y",
                "ajaxPath": '<?=$ajaxPath?>',
                "widgetBtnWrapId": '<?=$widgetBtnWrapId?>',
                "wrapperId": "<?=$wrapperId?>",
                "formId": '<?=$formId?>',
                "widgetBtnId": '<?=$widgetBtnId?>',
                "messageNodeId": '<?=$messageNodeId?>',
                "submitBtnId": '<?=$submitBtnId?>',
                "appResultBlockId": '<?=$appResultBlockId?>',
                "dataKeys": {
                    "clinicsKey": '<?=$clinicsKey?>',
                    "specialtiesKey": '<?=$specialtiesKey?>',
                    "servicesKey": '<?=$servicesKey?>',
                    "employeesKey": '<?=$employeesKey?>',
                    "scheduleKey": '<?=$scheduleKey?>',
                },
                "selectionNodes": {
                    ['<?=$clinicsKey?>']: {
                        "blockId": "<?=$clinicsKey?>_block",
                        "listId": "<?=$clinicsKey?>_list",
                        "selectedId": "<?=$clinicsKey?>_selected",
                        "inputId": "<?=$clinicsKey?>_value",
                        "isRequired": true
                    },
                    ['<?=$specialtiesKey?>']: {
                        "blockId": "<?=$specialtiesKey?>_block",
                        "listId": "<?=$specialtiesKey?>_list",
                        "selectedId": "<?=$specialtiesKey?>_selected",
                        "inputId": "<?=$specialtiesKey?>_value",
                        "isRequired": true
                    },
                    ['<?=$servicesKey?>']: {
                        "blockId": "<?=$servicesKey?>_block",
                        "listId": "<?=$servicesKey?>_list",
                        "selectedId": "<?=$servicesKey?>_selected",
                        "inputId": "<?=$servicesKey?>_value",
                        "isRequired": true
                    },
                    ['<?=$employeesKey?>']: {
                        "blockId": "<?=$employeesKey?>_block",
                        "listId": "<?=$employeesKey?>_list",
                        "selectedId": "<?=$employeesKey?>_selected",
                        "inputId": "<?=$employeesKey?>_value",
                        "isRequired": true
                    },
                    ['<?=$scheduleKey?>']: {
                        "blockId": "<?=$scheduleKey?>_block",
                        "listId": "<?=$scheduleKey?>_list",
                        "selectedId": "<?=$scheduleKey?>_selected",
                        "inputId": "<?=$scheduleKey?>_value",
                        "isRequired": true
                    }
                },
                "textNodes": {
                    "name": {
                        "inputId": "<?=$nameInputId?>",
                        "isRequired": true
                    },
                    "surname": {
                        "inputId": "<?=$surnameInputId?>",
                        "isRequired": true
                    },
                    "middleName": {
                        "inputId": "<?=$middleNameInputId?>",
                        "isRequired": true
                    },
                    "phone": {
                        "inputId": "<?=$phoneInputId?>",
                        "isRequired": true
                    },
                    "comment": {
                        "inputId": "<?=$commentInputId?>",
                        "isRequired": false
                    },
                },
                "defaultText": {
                    ['<?=$clinicsKey?>']: '<?=$blocksInfo[$clinicsKey]?>',
                    ['<?=$specialtiesKey?>']: '<?=$blocksInfo[$specialtiesKey]?>',
                    ['<?=$servicesKey?>']: '<?=$blocksInfo[$servicesKey]?>',
                    ['<?=$employeesKey?>']: '<?=$blocksInfo[$employeesKey]?>',
                    ['<?=$scheduleKey?>']: '<?=$blocksInfo[$scheduleKey]?>',
                },
                "isUpdate": false,
            });
        })
    </script>
<?php
}
?>

