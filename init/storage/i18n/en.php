<?php

return array(
    'lang'=>'en',
    'language'=>'Language',
    'app'=>array(
        'title'=>array(
            'login'=>'Login Roulette',
            'index'=>'Categories',
            'roulette'=>'Roulette',
            'win'=>'Winnings',
            'history'=>'History of winnings'
        ),
        'msg'=>array(
            'code'=>'Wrong security code!',
            'noVp'=>'You have no bonuses! Login is not possible.',
            'login'=>'Authorization succeeded',
            'noChar'=>'You don\'t have characters! Login is not possible.',
            'noAcc'=>'Invalid login or password!',
            'inputs'=>'Incorrectly entered data!',
            'error'=>'Error.',
            'vpMin'=>'Not enough bonuses.',
            'itemSendError'=>'Error! Please try again later.',
            'itemSend'=>'Sent.',

        ),
        'login'=>array(
            'inputLogin'=>'Login',
            'inputPass'=>'Password',
            'inputCaptcha'=>'Code protection',
            'btnLogin'=>'Login'
        ),
        'roulette'=>array(
            'winList'=>'Your winnings',
            'itemsEmpty'=>'no items'
        ),
        'win'=>array(
            'head'=>'Winnings',
            'list'=>'List',
            'colItem'=>'Item',
            'colName'=>'Name',
            'colDate'=>'Date win',
            'colChars'=>'Character',
            'btnSend'=>'Send',
            'noWin'=>'No winnings'
        ),
        'history'=>array(
            'head'=>'History',
            'list'=>'List',
            'colDate'=>'date',
            'colType'=>'Type',
            'colAction'=>'Action',
            'colChar'=>'Character',
            'itemSend'=>'sent',
            'itemNoSend'=>'not sent',
            'vpSend'=>'Came with',
            'vpNoSend'=>'Did not come with',
            'noHistory'=>'No history'
        ),
        'menu'=>array(
            'admin'=>'Admin',
            'home'=>'Main',
            'history'=>'History',
            'win'=>'Winnings',
            'exit'=>'Exit'
        ),
        'category'=>array(
            'head'=>'Categories',
            'noCategory'=>'No categories'
        )
    ),
    'admin' => array(
        'modal' => array(
            'delete' => 'Delete',
            'btnCancel' => 'Cancel',
            'btnDelete' => 'Delete'
        ),
        'menu' => array(
            'roulette' => 'roulette',
            'macros' => 'Macros',
            'category' => 'Categories',
            'addItem' => 'Add item to category',
            'logs' => 'Logs'
        ),
        'logs' => array(
            'headList' => 'log Files',
            'noLogs' => 'No logs'
        ),
        'index' => array(
            'head'=>'Home',
            'vpDay' => 'The bonuses awarded for days',
            'colName' => 'Name',
            'colCount' => 'Number',
            'countItemsWin' => 'The entire cast of things',
            'countSendItems' => 'Things sent total',
            'spentVp' => 'spend bonuses',
            'sendItemsDay' => 'Sent for today',
            'spentVpDay' => 'Spent bonuses for today'
        ),
        'macros' => array(
            'list'=>'List',
            'headList' => 'Macros',
            'headAdd' => 'Add Macro',
            'headEdit' => 'Edit Macro',
            'description' => '<p>Macro in advance prepared GM team for soap Protocol.</p>
<p>1. Invent a name for yourself</p>
<p>2.Write the GM team where there is the name of a character in this quote are replaced the words on the value enclosed in the character "#"</p>
<p>3.Available to dependent Word to insert the name of the character script "{charName}"</p>
<p>4.Macros about how it should be:</p>
<p class="well" style="font-family: monospace;">
.send money {charName} "gold" "gold #Gold#" #Gold# <br />
.send items {charName}, "Item", "Subject" #itemID# <br />
.character level {charName} #level# <br />
// and further that the similar
</p>',

            'colName' => 'Name',
            'colCmd' => 'Command',
            'deleteMacros' => 'Delete Macro?',
            'noMacros' => 'No macros',
            'inputName' => 'Name',
            'inputMacros' => 'Macro',
            'btnEdit' => 'Edit',
            'btnAdd' => 'Create'
        ),
        'category' => array(
            'headList' => 'Categories',
            'list'=>'List',
            'headAdd' => 'Add category',
            'headEdit' => 'Edit category',
            'colIcon' => 'Icon',
            'colName' => 'Name',
            'colDesc' => 'Description',
            'colPrice' => 'Price',
            'deleteCategory' => 'Delete a category? <br> Attention all items in the category will be deleted!',
            'noCategory' => 'No categories',
            'btnAdd' => 'Create',
            'inputIcon' => 'ItemId from WowHead or title pictures without the extension (.jpg)',
            'inputName' => 'Name',
            'inputDesc' => 'Description',
            'inputVp' => 'Price VP',
            'btnEdit' => 'Edit'
        ),
        'item' => array(
            'headList' => 'Items',
            'list'=>'List',
            'headAdd' => 'Add item',
            'headEdit' => 'Change item',
            'colName' => 'Name',
            'colMacro' => 'Macro',
            'colDesc' => 'Description',
            'deleteItem' => 'Delete item ?',
            'noItems' => 'No items',
            'selMacros' => 'Select a macro',
            'selCategory' => 'Select category',
            'inputIcon' => 'itemID from WowHead or the picture name without the extension (.jpg)',
            'inputName' => 'Name',
            'inputParse' => 'Type \'itemID \' for parsing data or do not fill this field',
            'btnAdd' => 'Add',
            'btnEdit' => 'Edit',
            'noAdd' => 'no macros or categories! adding not possible!',
            'noItem' => 'no Items !'
        ),
        'msg' => array(
            'delete' => 'Deleted',
            'error' => 'Error !',
            'deleteMacros' => 'Delete not possible! A macro used in the subject!',
            'valid' => 'Incorrectly entered data!',
            'edit' => 'Changed',
            'add' => 'Added',
            'macros' => 'Macros are not filled!',

        )
    ),
    'install' => array(
        'start' => 'Install <a href="{url}">Go</a>',
        'linkHome' => 'Main',
        'steps' => array(
            'index' => 'Checking for writable directories',
            'roulette' => '<p> roulette </p> configuration <p class="text-danger"> Attention! Don\'t forget to create a base for roulette or specify what you want it to install </p>',
            'wowdata' => 'WOW Server database configuration',

            'votedata' => '<p>bonus configuration table:</p>
<p>If you have a table with bonuses then you need to specify the name of the table the column account name  + column name account name + column name bonuses</p>
<p>If you don\'t have a table that you want to put the box in the installation tables vote and specify:</p>
<p>the database where you want to install</p>
<p>table name: vote</p>
<p>name of account: name name column</p>
<p>column name bonuses: vote</p>',

            'soap' => 'Soap Configuration Protocol Server WOW <p class="text-danger"> don\'t forget to configure the Soap server! </p>',

            'parser' => '<p>configuration parser votes</p>
<p>If you have your own parser then you can skip this step</p>
<p>If not then please fill out the form</p>
<p>That would add another top then you need to fill in a form again</p>
<p class="text-danger">Attention! Here you can add tops that have server statistics file on this formula:</p>
<pre class="text-danger">[ID] [date] [time] [ip address] [character name] [bonuses]</pre>
<pre class="text-danger">63734115 01.10.2015 01:10:40 95.47.3.48 testuser 1</pre>',

            'admin' => 'Roulette Administrator <p class="text-danger"> you can add only one Admin! </p>',
            'tables' => 'Installation of the roulette tables'
        ),
        'msg' => array(
            'admin' => 'Added to the Admin account {admin} Admin role',
            'serverCfg' => 'No wow server configuration!',
            'linkTo' => 'Go to roulette',
            'inputEmpty' => 'Not all filled!',
            'delete' => 'Deleted',
            'addTop' => 'Added',
            'accountEmpty' => 'No account!'
        )
    ),
    'formsInstall' => array(
        'checkVote' => 'Install table vote?',
        'checkData' => 'Add data to the tables for a test?',
        'btnEdit' => 'Edit',
        'btnNext' => 'Next',
        'btnPrev' => 'Ago',
        'btnSave' => 'Save',
        'btnAdd' => 'Add',
        'btnInstall' => 'Install',
        'btnInstallTable' => 'Install tables',
        'btnEmpty' => 'Refill',
        'inputHost' => 'Host',
        'inputPort' => 'Port',
        'inputUser' => 'User',
        'inputPass' => 'Password',
        'inputDB' => 'database Name',
        'inputAuth' => 'Base accounts WoW',
        'inputChar' => 'Base characters WoW',
        'inputTable' => 'Table Name',
        'inputColVp' => 'Column name bonuses',
        'inputColName' => 'Column name account name',
        'inputGmName' => 'Account with GM level 3',
        'inputGmPass' => 'Account password with GM level 3',
        'inputTopSite' => 'The name of the top',
        'inputTopFile' => 'Server statistics file reference',
        'inputTopVp' => 'The amount of bonuses for 1 voice',
        'inputAdmin' => 'Account ID with the accounts database'
    )
);