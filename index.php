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
	$item = ShoppingListItem::findById($_GET['ShoppingList']);
	if($item){
        $form = Form::editForm(
            "index.php", FORMS_PATH.DS.'formInput.yaml', "ShoppingList", $item);
	}
}elseif(isset($_GET['TodoList'])){
	$item = TodoList::findById($_GET['TodoList']);
	if($item){
        $form = Form::editForm(
            "index.php", FORMS_PATH.DS.'todoInputs.yaml', "TodoList", $item);
	}
}

$now=time();
$now = array(
			'date'=>strftime("%m/%d/%g",$now),
			'time'=>strftime("%H:%M",$now)
);

$header = "<html><head>";
$header.= "<script src=\"http://code.jquery.com/jquery-latest.js\"></script>";
$header.= "<link rel=\"stylesheet\" type=\"text/css\" href=\"stylesheets/main.css\">";
$header.= "</head>";
$header.= "<body>";

$formInput = FORMS_PATH.DS."formInput.yaml";
$form = !isset($form) ? new Form("index.php", $formInput, "ShoppingList") : $form;



$intro   ="<section id=\"intro\">";
$intro  .="<button id=\"toggle\" class=\"ShoppingList\" type=\"button\" >switch to TODOs</button>";

$intro  .="<div id=\"mutableForm\">";
$intro  .=$form->toString();
$intro  .="</div>";

$toggler    = "<script href=\"js/toggler.js\" type=\"text/javascript\"></script>";
$confirm    = "<script href=\"js/confirm.js\" type=\"text/javascript\"></script>";
$script     = $toggler.$confirm;

$intro  .=$script;
$intro  .="<a href=\"printme.php\">printable</a>";
$intro  .="</section>";// id=\"intro\">";

//----------------------intro done

$logger->log(0,"index.php::buildPage()", "presenting form defined in {$formInput}");

$content = "<div id=\"content\">";
$content.="<div id=\"content_left\">";


$shoppingList = Lists::parseGroceryList(Lists::getList("ShoppingList"));

$list    = "<div id=\"grocery_list\">";
$list   .="<h3>Shopping</h3>";

$list   .= ListView::RenderList($shoppingList);
$list   .="</div>";

$content.=$list."</div>";

//$content.="<div id=\"content_right\">";

//$todoList = Lists::parseTodoList(Lists::getList("TodoList"));
//krumo($todoList);
//$list= "<div id=\"todo_list\">";
//$list.="<h3>TODOs</h3>";
//
//$list.= ListView::RenderTodoList($todoList);
//$list.="</div>";

//$content.=$list."</div>";
$content.= "</div>";// id=\"content\">";

//NEXT STEPS...
//	instantiate ListItem from POST array, write it out to file, after checking first to know whether it already exists
//		check length of file for too much length.



$closure=  "<body/></html>";


echo $header.$intro.$content.$closure;
