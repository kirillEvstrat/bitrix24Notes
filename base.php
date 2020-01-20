<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

function writeToLog($data) {
        $log = "\n------------------------\n";
        $log .= print_r($data, 1);
        $log .= "\n------------------------\n";
        file_put_contents($_SERVER["DOCUMENT_ROOT"]."/log228.txt", $log, FILE_APPEND);
}
if ($ex = $APPLICATION->GetException())
               echo $ex->GetString();
///CRM start////////////////
//////////////////////
////////////////////
$res = \CCrmStatus::GetList(array('SORT' => 'ASC'), ['ENTITY_ID'=> "SOURCE", "STATUS_ID"=>$lead['SOURCE_ID']]);// источники лида
// категории сделок 
$categories = \Bitrix\Crm\Category\DealCategory::getAll();
    foreach ($categories as $category){
        var_dump($category['ID']);
        var_dump($category["NAME"]);
    }
$res = \CCrmLead::GetStatuses(); //статусы лида
$companyIDs = \Bitrix\Crm\Binding\ContactCompanyTable::getContactCompanyIDs($contactID); //Класс ContactCompanyTable
$deal =  \CCrmDeal::GetListEx([],["ID"=>intval($dealId), "CHECK_PERMISSIONS"=>"N"],false,false,  ["*", "UF_*"])->Fetch();//список сделок
//направления сделки
 $arResult['CATEGORIES'] = \Bitrix\Crm\Category\DealCategory::getAll(true); 
/// обновление счета
$fields = array(
             'UF_CRM_1562855359' => $title,
             'UF_CRM_1562855373' => $date,

         );

         try {
             $res = $CCrmInvoice->Update($invoiceId, $fields);
         } catch (Main\DB\SqlQueryException $e) {
             self::writeToLog($e);
         }
//получить значение списка
       $res = \CUserFieldEnum::GetList([], ["ID" => $elID]);
        if(intval($res->SelectedRowsCount())>0) {
            $answer = $res->Fetch()['VALUE'];
        }
///  FieldMulti (например phone)
$rs = \CCrmFieldMulti::GetList(array(), array('ENTITY_ID'  => 'CONTACT',
                'ELEMENT_ID' => $Contact_ID,
                'TYPE_ID'    => 'PHONE',
                'VALUE_TYPE' => 'MOBILE',));
// фиксация событий в истории
$eventEntity = new \CCrmEvent();
$res = $eventEntity->Add(
                array(
                    'USER_ID' => $arFields['MODIFY_BY_ID'],
                    'ENTITY_ID' => $arFields['ID'],
                    'ENTITY_TYPE' => \CCrmOwnerType::LeadName,
                    'EVENT_TYPE' => \CCrmEvent::TYPE_CHANGE,
                    'EVENT_NAME' => "Измененено поле 'Дата для отчёта'",

                    'EVENT_TEXT_1' => $oldLead[self::REPORT_DATE],
                    'EVENT_TEXT_2' => $arFields[self::REPORT_DATE]
                ),
                false
);
/////////////////////////////
//////// CRM end ////////////
//////////////////////////////


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

// сообщения в живую ленту //
CModule::IncludeModule("blog");
CModule::IncludeModule("socialnetwork");
       $arBlog = CBlog::GetByOwnerID(1);
       $arFields= array(
           "TITLE" => "Заголовок",
           "DETAIL_TEXT" => "Описание",
           "DATE_PUBLISH" => date('d.m.Y H:i:s'),
           "PUBLISH_STATUS" => "P",
           "CATEGORY_ID" => "",
           "PATH" => "/company/personal/user/1/blog/#post_id#/",
           "URL" => "admin-blog-s1",
           "PERMS_POST" => Array("12" => "WC"),
           "PERMS_COMMENT" => Array (),
           "SOCNET_RIGHTS" => Array
           (

           ),
           "=DATE_CREATE" => "now()",
           "AUTHOR_ID" => "1",
           "BLOG_ID" => $arBlog['ID'],
       );
       $newID= CBlogPost::Add($arFields);
       $arFields["ID"] = $newID;
       $arParamsNotify = Array(
           "bSoNet" => true,
           "UserID" => "1",
           "user_id" => "1",
       );
       $a = CBlogPost::Notify($arFields, array(), $arParamsNotify);

// авторизация, изменение групп 

$users = CUser::GetList($by="ID", $order="desc", Array('UF_DEPARTMENT'=>"90", "ACTIVE"=>"Y"), ['CHECK_PERMISSOINS' => 'N', 'SELECT' => ['*', "UF_*"]]);

$user = new  CUser;
$fields = ['PASSWORD'=>$pass1, 'CONFIRM_PASSWORD'=>$pass2];
$user -> update($_POST['USER_ID'], $fields, false);
//$arAuthResult = $user->Login($_POST["USER_LOGIN"], $pass1);
$user->Authorize($_POST['USER_ID'], false);
$arGroups = \CUser::GetUserGroup(intval($_POST["USER_ID"]));
$arGroups[] = intval(12);
\CUser::SetUserGroup(intval($_POST['USER_ID']), $arGroups);
$arPolicy = \CUser::GetGroupPolicy($_POST["USER_ID"]); //получить список требований к пароля исходя из групп пользователя
$errors = \CUser::CheckPasswordAgainstPolicy($pass1, $arPolicy); // проверка на соответсвия пароля правилам безопасности 
//получение начальника пользователя 
$arManagers = \CIntranetUtils::GetDepartmentManager($user["UF_DEPARTMENT"], $user["ID"], true);
        foreach ($arManagers as $key => $arManager) {
            $managerID = $arManager['ID'];
        }
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
//получение элемента и его свойства
   $elements = \CIBlockElement::GetList(["ID"=>"DESC"], ["=IBLOCK_ID" => $IBlockID,  "=PROPERTY_ID_TOVARA" => $productID]);
        while($element = $elements->GetNextElement()){
            $arFields = $element->GetFields();
       	    $arProps = $element->GetProperties();
	}
//или
    $lastElementID = $elements->Fetch()['ID'];
    $lastElementProp= \CIBlockElement::GetProperty($IBlockID, $lastElementID, [], ['ID'=>$PropertyID])->Fetch();

        
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
/// Запуск любого БП в срм (\bitrix\js\bizproc\starter.js - там вся обработка запуска);
///  запуск через  js
CJSCore::Init('bp_starter');
var config = {
	documentId : 'DEAL_<?=$dealId?>',
	moduleId :'crm',
	entity : 'CCrmDocumentDeal',
	documentType : 'DEAL',
	templateId : 97
};
BX.Bizproc.Starter.singleStart(config);



//////Highload-блоками////////
////////////////////////////
//Подготовка:
if (CModule::IncludeModule('highloadblock')) {
   $arHLBlock = Bitrix\Highloadblock\HighloadBlockTable::getById(1)->fetch();
   $obEntity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arHLBlock);
   $strEntityDataClass = $obEntity->getDataClass();
}

//Добавление:
if (CModule::IncludeModule('highloadblock')) {
   $arElementFields = array(
      'UF_NAME' => $arPost['name'],
      'UF_MESSAGE' => $arPost['message'],
      'UF_DATETIME' => new \Bitrix\Main\Type\DateTime
   );
   $obResult = $strEntityDataClass::add($arElementFields);
   $ID = $obResult->getID();
   $bSuccess = $obResult->isSuccess();
}

//Получение списка:
if (CModule::IncludeModule('highloadblock')) {
   $rsData = $strEntityDataClass::getList(array(
      'select' => array('ID','UF_NAME','UF_MESSAGE','UF_DATETIME'),
      'order' => array('ID' => 'ASC'),
      'limit' => '50',
   ));
   while ($arItem = $rsData->Fetch()) {
      $arItems[] = $arItem;
   }
}
Выбор случайного значения:
$q = new Entity\Query($entity);
       $q->setSelect(array('*'));
       $q->setFilter($arFilter);
       $q->setLimit(1);
       $q->registerRuntimeField(
           'RAND', array('data_type' => 'float', 'expression' => array('RAND()'))
       );
       $q->addOrder("RAND", "ASC");
       $result = $q->exec();



///////посмотреть компонент user.selector
bitrix/components/bitrix/report.view/templates/.default/template.php

//////  ДИСК //////
        
//добавить запись о файле в таблицу хранения 
    $arFile = CFile::MakeFileArray($pathtofile);
    $arFile['MODULE_ID'] = 'disk';
    $fid = CFile::SaveFile($arFile, 'disk');
    $fileIDs[] = $fid;
//параметры для поиска хранилища в бд, таблица b_disk_storage
        $dbDisk = \Bitrix\Disk\Storage::getList(array("filter"=>array("ENTITY_ID" => "shared_files_s1", "ENTITY_TYPE" => 'Bitrix\Disk\ProxyType\Common')));
        if ($arDisk = $dbDisk->Fetch()) {
        $storage = \Bitrix\Disk\Storage::loadById($arDisk["ID"]);
        }
 //получение папки и запись файла в нее   
 $folder = Bitrix\Disk\SpecificFolder::getFolder($storage, "RESOURCE");
 foreach($fileIDs as $k => $fileId) {
        $fileArray = \CFile::getById($fileId)->fetch();
       
            $file = $folder->addFile(array(
                'NAME' => $fileArray['FILE_NAME'],
                'FILE_ID' => $fileId,
                'CONTENT_PROVIDER' => null,
                'SIZE' => $fileArray['FILE_SIZE'],
                'CREATED_BY' => 1,
                'UPDATE_TIME' => null,
            ), array(), true);
}
///// экспорт инфоблока в xml
$obExport = new \CIBlockCMLExport;

        $NS = [
            "IBLOCK_ID" => $IBlockID,
            "STEP" =>1,
            "SECTIONS_FILTER"=> "active",
            "ELEMENTS_FILTER"=>"active",
        ];
        $fp = fopen($fileDir.$fileName.".xml", "ab");
        if($obExport->Init($fp, $NS["IBLOCK_ID"], $NS["next_step"], true, $fileDir, $fileName))
        {  
                //$_SESSION["BX_CML2_EXPORT"] у меня пустой 
                //$fileName = (new \DateTime())->format("d_m_Y__h_i_s"); $fileDir = $_SERVER['DOCUMENT_ROOT']."/upload/disk/";
            $obExport->StartExport();
            $obExport->StartExportMetadata();
            $obExport->ExportProperties($_SESSION["BX_CML2_EXPORT"]["PROPERTY_MAP"]);
            $result = $obExport->ExportSections(
                $_SESSION["BX_CML2_EXPORT"]["SECTION_MAP"],
                $start_time,
                $INTERVAL,
                $NS["SECTIONS_FILTER"],
                $_SESSION["BX_CML2_EXPORT"]["PROPERTY_MAP"]
            );
            $obExport->EndExportMetadata();
            $obExport->StartExportCatalog();
            $result = $obExport->ExportElements(
                $_SESSION["BX_CML2_EXPORT"]["PROPERTY_MAP"],
                $_SESSION["BX_CML2_EXPORT"]["SECTION_MAP"],
                $start_time,
                $INTERVAL,
                0,
                $NS["ELEMENTS_FILTER"]
            );
            $obExport->EndExportCatalog();
            $obExport->ExportProductSets();
            $obExport->EndExport();
            return true;
        }
