<?
function writeToLog($data) {
        $log = "\n------------------------\n";
        $log .= print_r($data, 1);
        $log .= "\n------------------------\n";
        file_put_contents($_SERVER["DOCUMENT_ROOT"]."/log228.txt", $log, FILE_APPEND);
}
///CRM start////////////////
//////////////////////
$deal =  \CCrmDeal::GetListEx([],["ID"=>intval($dealId), "CHECK_PERMISSIONS"=>"N"],false,false,  ["*", "UF_*"])->Fetch();
//получить значение списка
       $res = \CUserFieldEnum::GetList([], ["ID" => $elID]);
        if(intval($res->SelectedRowsCount())>0) {
            $answer = $res->Fetch()['VALUE'];
        }
//////// CRM end ////////////

// отправка уведомлений в колокольчик
\CModule::IncludeModule('im');
\CModule::IncludeModule("intranet");
function sendNotify($title, $message, $userIdTo, $userIdFrom){
        \CIMMessenger::Add(array(
            'TITLE' => $title,
            'MESSAGE' => $message,
            'TO_USER_ID' => $userIdTo,
            'FROM_USER_ID' => $userIdFrom,
            'MESSAGE_TYPE' => 'S', # P - private chat, G - group chat, S - notification
            'NOTIFY_MODULE' => 'intranet',
            'NOTIFY_TYPE' => 2,  # 1 - confirm, 2 - notify single from, 4 - notify single
        ));
    }

// авторизация, изменение групп 

$user = new  CUser;
$fields = ['PASSWORD'=>$pass1, 'CONFIRM_PASSWORD'=>$pass2];
$user -> update($_POST['USER_ID'], $fields, false);
//$arAuthResult = $user->Login($_POST["USER_LOGIN"], $pass1);
$user->Authorize($_POST['USER_ID'], false);
$arGroups = \CUser::GetUserGroup(intval($_POST["USER_ID"]));
$arGroups[] = intval(12);
\CUser::SetUserGroup(intval($_POST['USER_ID']), $arGroups);

// ДОБАВЛЕНИЕ елемента инфоблока
\CModule::IncludeModule("iblock");

        $el = new \CIBlockElement;
        $PROP = array();
        $PROP[164] = $arFields['ID'];
        $PROP[165] = $arFields['UF_DEAL_ID'];
        $arLoadProductArray = Array(
            "MODIFIED_BY"    => $USER->GetID(), // элемент изменен текущим пользователем
            "IBLOCK_SECTION_ID" => false,          // элемент лежит в корне раздела
            "IBLOCK_ID"      => 39,
            "PROPERTY_VALUES"=> $PROP,
            "NAME"           => "счет",
            "ACTIVE"         => "Y",            // активен
        );
        $idOfEl = $el->Add($arLoadProductArray);
        
//запуск бп
\Bitrix\Main\Loader::includeModule('bizproc');

        $arErrorsTmp = array();
        $wfId = \CBPDocument::StartWorkflow(
            115,
            [ 'lists', 'Bitrix\Lists\BizprocDocumentLists' , $idOfEl ],
            [],
            $arErrorsTmp
        );
//узнать парметры для запуска бп (запускать в самом бп)
$rootActivity = $this->GetRootActivity();
$documentId = $rootActivity->GetDocumentId();
$rootActivity->SetVariable("test", print_r($documentId,true));       













?>
