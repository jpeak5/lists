<?php
require_once("config.php");
require_once(SPYC);

global $logger;

if(isset($_POST['submit'])){
	SubmitHandler::process($_POST);
}


$result = false;

if(isset($_GET['ShoppingList'])){
	//then this is a shopping list edit
	$item = ShoppingList::findById($_GET['ShoppingList']);
	if($item){
//		krumo($item);
		$form = Form::editForm("index.php", FORMS_PATH.DS.'formInput.yaml', "ShoppingList", $item);
	}
}elseif(isset($_GET['TodoList'])){
	$item = TodoList::findById($_GET['TodoList']);
	if($item){
		$form = Form::editForm("index.php", FORMS_PATH.DS.'todoInputs.yaml', "TodoList", $item);
	}
}








$now=time();
$now = array(
			'date'=>strftime("%m/%d/%g",$now),
			'time'=>strftime("%H:%M",$now)
);


$header = "<html><head>";
$header.="<script src=\"http://code.jquery.com/jquery-latest.js\"></script>";
$header.="<link rel=\"stylesheet\" type=\"text/css\" href=\"stylesheets/main.css\">";
$header.="</head>";
$header.="<body>";

$formInput = FORMS_PATH.DS."formInput.yaml";
$form = !isset($form) ? new Form("index.php", $formInput, "ShoppingList") : $form;



$intro="<section id=\"intro\">";
$intro .="<button id=\"toggle\" class=\"ShoppingList\" type=\"button\" >switch to TODOs</button>";

$intro.="<div id=\"mutableForm\">";
$intro.=$form->toString();
$intro.="</div>";

$script = "<script>";
$script.="/* attach a submit handler to the form */";
$script.="$(\"#toggle\").click(function(event){";
$script.="console.log(\"beginning the script\");";
$script.="/* stop form from submitting normally */";
$script.="event.preventDefault();";

$script.="/* get some values from elements on the page: */";
$script.="var \$currentState = $(this),";
$script.="state = \$currentState.attr(\"class\"),";
$script.="url = \"form.php\";";
$script.="console.log(\"currentState= \"+\$currentState.attr(\"class\"));";

$script.="/* Send the data using post and put the results in a div */";
$script.="$.post(url, {";
$script.="form: state";
$script.="}, function(data){";
$script.="var content = $(data);";
$script.="$(\"#mutableForm\").empty().html(content);";
$script.="if(\$currentState.attr(\"class\")==\"ShoppingList\"){";
$script.="\$currentState.attr(\"class\", \"TodoList\");";
$script.="\$currentState.html(\"switch to Shopping\");";
$script.="}else{";
$script.="\$currentState.attr(\"class\", \"ShoppingList\");";
$script.="\$currentState.html(\"switch to Todo\");";
$script.="}";

$script.="});";

$script.="});";
$script.="</script>";

//new script
$script.="<script>";
$script.="function confirmDelete(delUrl) {";
$script.="  if (confirm(\"Are you sure you want to delete\")) {";
$script.="    document.location = delUrl;";
$script.="  }";
$script.="}";
$script.="</script>";


$intro.=$script;
$intro.="<a href=\"printme.php\">printable</a>";

$intro.="</section>";// id=\"intro\">";

//----------------------intro done

$logger->log(0,"index.php::buildPage()", "presenting form defined in {$formInput}");

$content = "<div id=\"content\">";
$content.="<div id=\"content_left\">";


$shoppingList = Lists::parseGroceryList(Lists::getList("ShoppingList"));
//echo "\$shoppingList\n";
//krumo($shoppingList);
$list = "<div id=\"grocery_list\">";
$list.="<h3>Shopping</h3>";

if(!empty($shoppingList)){
	foreach($shoppingList as $store=>$items){
		//	echo "\$store";
		//	krumo($store);
		$storeTotal = 0;
		$listHead="";
		$listHead.="<span class=\"heading\"><strong>".$store;
		$listBody = "<ul>";
		foreach($items as $item){
			$listBody.="<li class=\"litem\"><strong>{$item->item}</strong>&nbsp;(\${$item->price_estimate})";
			$listBody.="&nbsp;<a class=\"deleteme\" href=\"javascript:confirmDelete('delete.php?ShoppingList=".urlencode($item->id)."')\">delete</a>";
			$listBody.="&nbsp;<a class=\"editme\" href=\"index.php?ShoppingList=".urlencode($item->id)."\">edit</a>";
			$storeTotal+=isset($item->price_estimate) ? $item->price_estimate: 0;
			$listBody.=(strlen($item->description)>0)?"<br/><span class=\"description\"> ".$item->description."</span>":"";
			$listBody.="</li>";
		}
		$listHead.="</strong><span class=\"aggregate\">&nbsp;&nbsp;(\$".$storeTotal.")</span></span>";
		$listBody.="</ul>";
		$list.=$listHead.$listBody;
	}
}
$list.="</div>";

$content.=$list."</div>";

$content.="<div id=\"content_right\">";

$todoList = Lists::parseTodoList(Lists::getList("TodoList"));
//krumo($todoList);
$list= "<div id=\"todo_list\">";
$list.="<h3>TODOs</h3>";

if(!empty($todoList)){
	foreach($todoList as $doer=>$todos){
		//	echo "\$store";
		//	krumo($store);

		$doerTotal = 0;
		$listHead="";
		$listHead.="<span class=\"heading\"><strong>".$doer;
		$listBody="<ul>";
		foreach($todos as $todo){
			$listBody.="<li class=\"litem\"><strong>{$todo->item}</strong> &nbsp;({$todo->duration})";
			$listBody.="&nbsp;<a class=\"deleteme\" href=\"javascript:confirmDelete('delete.php?TodoList=".urlencode($todo->id)."')\">delete</a>";
			$listBody.="&nbsp;<a class=\"editme\" href=\"index.php?TodoList=".urlencode($todo->id)."\">edit</a>";
			$doerTotal+=isset($todo->duration) ? $todo->duration: 0;
			$listBody.=(strlen($todo->description)>0)?"<br/><span class=\"description\">".$todo->description."</span>":"";
			$listBody.="</li>";
		}
		$listHead.="</strong><span class=\"workAggregate\">&nbsp;&nbsp;(".$doerTotal.")</span></span>";
		$listBody.="</ul>";
		$list.=$listHead.$listBody;
	}
}
$list.="</div>";

$content.=$list."</div>";
$content.= "</div>";// id=\"content\">";

//NEXT STEPS...
//	instantiate ListItem from POST array, write it out to file, after checking first to know whether it already exists
//		check length of file for too much length.



$closure=  "<body/></html>";


echo $header.$intro.$content.$closure;