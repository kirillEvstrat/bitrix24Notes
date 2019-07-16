// отправка уведомлений в колокольчик
 private function sendNotify($title, $message, $userIdTo, $userIdFrom){
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
