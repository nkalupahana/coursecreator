<?php
session_start();
require "database.php";
require "tablefunctions.php";

$cid = escape($_GET["id"]);
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;
$_SESSION["editcourse"] = $cid;

$conn = connect("coursecreator");

$structure = queryRow($conn, "SELECT structured AS struc FROM createdCourses WHERE id=$id AND cid=$cid", "struc");

$tableInsert = "";
$tableString = "";
$createButton = "";

if ($structure == "1") {
    echo "<script>var isStructured=true; </script>";
    $createButton = "<a class='dropdown-button btn' data-activates='makelesson'>Create a lesson</a>";
    
    $courseLessons = queryArray($conn, "SELECT * FROM $table ORDER BY rowOrder", true);
    $tableInsert = returnMainTable($courseLessons);
    
} else if ($structure == "0") {
    echo "<script>var isStructured=false; </script>";
    $sqldec = "SELECT * FROM " . $table . " WHERE NOT EXISTS (SELECT * FROM " . $table . " t WHERE t.block = " . $table . ".block AND t.rowOrder = " . $table . ".rowOrder - 1) AND id < 9999 ORDER BY rowOrder";
    
    $createButton = "<a class='waves-effect waves-light btn' onclick='openCreateModule()'>Create a module</a>";
    
    $limitedFront = queryArray($conn, $sqldec, true);
    $max = queryRow($conn, "SELECT MAX(id) AS id FROM $table WHERE id != 9999", "id");
    
    $tableString = returnAccordian($conn, $limitedFront, $max, $table);
    
} else {
    die("A structure is not defined for this course.");
}

$plugin = queryRow($conn, "SELECT tryitPlugin AS tryit FROM createdCourses WHERE id=$id AND cid=$cid", "tryit");
$tryitButton = "";
if ($plugin == "") {
    $tryitButton = "<a class='waves-effect waves-light btn' onclick=\"Materialize.toast('Functionality not implemented at this time', 5000)\">Add Tryit Editor</a>";
}

?>

<html>
<head>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="css/materialize.min.css">
    <script src="js/sortable.min.js"></script>
    <style>
        .sortable-ghost {
          opacity: .6;
        }
        
        .container {
            margin: 0 auto;
            max-width: none !important;
            width: 95% !important
        }

        @media only screen and (min-width: 601px) {
            .container {
                width: 92% !important
            }
        }

        @media only screen and (min-width: 993px) {
            .container {
                width: 90% !important
            }
        }

    </style>
</head>

<body>
<br><br><br><br>
<div class="container">
    <div id="containment">
        <?php echo $tableString; ?>
    </div>
    <table class="bordered">
    <thead>
      <tr>
          <th>Lesson Name</th>
          <th>Type</th>
          <th>Actions</th>
      </tr>
    </thead>

    <tbody id="movable">
        <?php echo $tableInsert; ?>
    </tbody>
    <tfoot>
        <tr>
          <td style="color: red;">Final Challenge (Create this last!)</td>
          <td>Final Challenge</td>
          <td><a href="finalchallenge">Edit</a></td>
        </tr>
      </tfoot>
    </table>
    <br>
    <div style="text-align: center;">
        <?php echo $createButton; ?>
        <a class="waves-effect waves-light btn" onclick="changeSortState()" id="reorderButton">Reorder Lessons</a>
        <a class="waves-effect waves-light btn" onclick="cancelSort()" style="display: none;" id="cancelButton">Cancel Order</a>
        <?php echo $tryitButton; ?>
    </div>
    <br><br><br>

</div>

<a class="btn-floating btn-large waves-effect waves-light red" style="position: absolute; top: 25px; left: 20px;" href="/coursecreator/dash"><i class="material-icons">arrow_back</i></a>

<ul id='makelesson' class='dropdown-content'>
    <li><a onclick="resetFields('v')" class="modal-trigger" href="#video">Video</a></li>
    <li><a onclick="resetFields('a')" class="modal-trigger" href="#activity">Activity</a></li>
</ul>

<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/materialize.min.js"></script>

<div id="video" class="modal">
    <div class="modal-content">
        <h4>Create a Video</h4>
        <p>Videos are for teaching. Teach concepts here.</p>
        <div class="row">
            <div class="input-field col s12">
              <input name="lessonNamev" id="lessonNamev" placeholder="Example Name: What is the stock market? (Video)" type="text" class="validate">
              <label for="lessonNamev">Name of Lesson</label>
            </div>
          </div>
    </div>
    <div class="modal-footer">
        <a class="modal-action modal-close waves-effect waves-red btn-flat ">Cancel</a>
        <a onclick="submit('v')" class="modal-action modal-close waves-effect waves-green btn-flat ">Submit</a>
    </div>
</div>

<div id="activity" class="modal">
    <div class="modal-content">
        <h4>Create an Activity</h4>
        <div class="row">
            <div class="input-field col s12">
              <input name="lessonNamea" id="lessonNamea" placeholder="Example Name: What is the stock market? (Activity)" type="text" class="validate">
              <label for="lessonNamea">Name of Lesson</label>
            </div>
          </div>
    </div>
    <div class="modal-footer">
        <a class="modal-action modal-close waves-effect waves-red btn-flat ">Cancel</a>
        <a onclick="submit('a')" class="modal-action modal-close waves-effect waves-green btn-flat ">Submit</a>
    </div>
</div>

<div id="renameModal" class="modal">
    <div class="modal-content">
        <h4>Rename Module</h4>
        <div class="row">
            <div class="input-field col s12">
              <input value="" id="mname" type="text" class="validate">
              <label for="mname">Module Name</label>
            </div>
            <div class="input-field col s12" style="margin-top: 10px;">
              <input value="" id="mdesc" type="text" class="validate">
              <label for="mdesc">Module Description</label>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a class="modal-action modal-close waves-effect waves-red btn-flat ">Cancel</a>
        <a onclick="updateNames()" class="modal-action modal-close waves-effect waves-green btn-flat ">Submit</a>
    </div>
</div>

<div id="module" class="modal">
    <div class="modal-content">
        <h4>Create a Module</h4>
        <p>Modules are little packs of videos and exercises - mini-courses, if you will. Students can skip from module to module, but they can't skip between videos and activities within modules.</p>
        <div class="row">
            <div class="input-field col s12">
              <input name="moduleName" id="moduleName" placeholder="Example Name: Metaphors" type="text" class="validate">
              <label for="moduleName">Name of Module</label>
            </div>
        </div>
        <p>We ask that you create a video along with this module to get started. Don't worry, you can re-order everything later.</p>
        <div class="row">
            <div class="input-field col s12">
              <input name="lessonNamem" id="lessonNamem" placeholder="Example Name: What is the stock market? (video)" type="text" class="validate">
              <label for="lessonNamem">Name of Video</label>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a class="modal-action modal-close waves-effect waves-red btn-flat ">Cancel</a>
        <a onclick="createModule()" class="modal-action modal-close waves-effect waves-green btn-flat ">Submit</a>
    </div>
</div>

<div id="fold-fab" style="display: none; bottom: 45px; right: 24px;" class="fixed-action-btn">
  <a onclick="changeFoldState()" class="btn-floating btn-large waves-effect waves-light red">
    <i id="fold-icon" class="material-icons">unfold_less</i>
  </a>
</div>

<script>
    var sortable;
    var idNotepad = "";
    var open = false;
    
    $(document).ready(function(){
        $('.modal').modal();
        sortable = Sortable.create(movable, { group: 'lessons', animation: 100, disabled: true, });
     });
     
    
    
    function changeSortState() {
        var state = sortable.option("disabled");
        if (state) {
            document.getElementById("cancelButton").style.display = "inline-block";
            if (isStructured) {
                Materialize.toast("Click and drag any row in the lessons table to order your lessons. Click on the 'Save Order' button to save.", 10000);
                document.getElementById("reorderButton").innerHTML = "Save Order";
            } else {
                alert("On the next screen, you can drag lessons inside or between modules, or click on the red button in the corner and drag modules to reorder them. Remember, the order of modules doesn't matter in an unstructured course, because students can take modules in any order, but if they look better in a certain way, you can reorder them here.");
                document.getElementById("reorderButton").innerHTML = "Save Order";
                document.getElementById("fold-fab").style.display = "block";
                
                var modules = $("#containment").children()[0].children;
                
                for (var i=0; i < modules.length; i++) {
                     $('.collapsible').collapsible('open', i);
                }
        
                open = true;
        
                for (var i=0; i < modules.length; i++) {
                    var child = modules[i];
                    var itemid = child.children[1].children[4].children[1].id;
            
                    var element = document.getElementById(itemid);
            
                    var sortObj = Sortable.create(element, { group: 'modules', filter: ".protect-item", animation: 100,
                        onRemove: function (/**Event*/evt) {
                            var itemLength = this.toArray().length;
                            if (itemLength == 1) {
                                var protectItem = this.el.children[0];
                                protectItem.classList.add("protect-item");
                                
                            }
                            
                        }, onAdd: function (/**Event*/evt) {
                    		var unprotect = this.el.children;
                            for (var j=0; j < unprotect.length; j++) {
                                unprotect[j].classList.remove("protect-item");
                                unprotect[j].setAttribute('draggable', true);
                            }
                            
                    	}, onFilter: function (/**Event*/evt) {
                    		Materialize.toast("Whoa! You have to keep at least one lesson in each module. If you want to delete this module but save the lesson, create another blank lesson in the module, get the lesson you want out, and delete the module.", 10000);
                    	},
                    });
                    
                    var itemLength = sortObj.toArray().length;
                    console.log(itemLength);
                    if (itemLength == 1) {
                        var protectItem = sortObj.el.children[0];
                        console.log(protectItem.id);
                        protectItem.classList.add("protect-item");
                        
                    }
                }
                
                var element = document.getElementById("moduleMachine");
                Sortable.create(element, { group: 'mainmodules', animation: 100 });
            }
        } else {
            document.getElementById("cancelButton").style.display = "none";
            if (isStructured) {
                var newIDArray = [];
                var itemCollection = document.getElementById("movable").children;
            
                console.log(itemCollection);
            
                for (var i = 0; i < itemCollection.length; i++) {
                    newIDArray.push(itemCollection[i].id);
                }
            
                var parameter = newIDArray.join(",");
            
                document.getElementById("movable").innerHTML = "<div class='preloader-wrapper active'> <div class='spinner-layer spinner-blue-only'> <div class='circle-clipper left'> <div class='circle'></div> </div><div class='gap-patch'> <div class='circle'></div> </div><div class='circle-clipper right'> <div class='circle'></div> </div> </div> </div>";
            } else {
                var sortOrder = $(".collapsible").find("tr.respect_item, div.collapsible-header");
                var idArr = [];
                
                for (var i=0; i < sortOrder.length; i++) {
                    var item = sortOrder[i].id;
                    
                    if (item == "") {
                        var title = sortOrder[i].children[0].children[0].innerHTML;
                        var description = sortOrder[i].parentElement.children[1].children[1].innerHTML;
                        idArr.push(title + "|" + description);
                    } else {
                        idArr.push(item);
                    }
                    
                }
                parameter = idArr.join("<|>");
                
                document.getElementById("containment").innerHTML = "<div class='preloader-wrapper active'> <div class='spinner-layer spinner-blue-only'> <div class='circle-clipper left'> <div class='circle'></div> </div><div class='gap-patch'> <div class='circle'></div> </div><div class='circle-clipper right'> <div class='circle'></div> </div> </div> </div>";
            }
            
            var typeParam = "";
            
            if (isStructured) {
                typeParam = "s";
            } else {
                typeParam = "u";
            }
            
            console.log(parameter);
            
            $.ajax({url: "ajax/reorderLessons.php?s=" + typeParam + "&q=" + parameter, success: function(result) {
                try {
                    console.log(result);
                    var arr = JSON.parse(result);
                    arr = arr.replace(/&#60;/g, "<");
                    arr = arr.replace(/&#62;/g, ">");
                    arr = arr.replace(/\\/g, '');
                    if (isStructured) {
                        document.getElementById("movable").innerHTML = arr;
                        Materialize.Toast.removeAll();
                    } else {
                        document.getElementById("containment").innerHTML = arr;
                        document.getElementById("fold-fab").style.display = "none";
                        $('.collapsible').collapsible();
                    }
                    document.getElementById("reorderButton").innerHTML = "Saved!";
                    setTimeout(function(){ document.getElementById("reorderButton").innerHTML = "Reorder Lessons";  }, 1000);
                } catch (err) {
                    console.log("User tried to work with nonexistent items.");
                    window.location.reload();
                }
                
             }});
            
        }
        
        sortable.option("disabled", !state);
    }
    
    function submit(parameter) {
        if (idNotepad == "") {
            document.getElementById("movable").innerHTML = "<div class='preloader-wrapper active'> <div class='spinner-layer spinner-blue-only'> <div class='circle-clipper left'> <div class='circle'></div> </div><div class='gap-patch'> <div class='circle'></div> </div><div class='circle-clipper right'> <div class='circle'></div> </div> </div> </div>";
            
            var lessonName = document.getElementById("lessonName" + parameter).value;
            $.ajax({url: "ajax/create.php?e=" + parameter + "&q=" + lessonName, success: function(result){
                try {
                    var arr = JSON.parse(result);
                    arr = arr.replace(/&#60;/g, "<");
                    arr = arr.replace(/&#62;/g, ">");
                    arr = arr.replace(/\\/g, '');
                    document.getElementById("movable").innerHTML = arr;
                } catch (err) {
                    console.log("User tried to work with nonexistent items.");
                    window.location.reload();
                }
                
             }});
        } else {
            document.getElementById("containment").innerHTML = "<div class='preloader-wrapper active'> <div class='spinner-layer spinner-blue-only'> <div class='circle-clipper left'> <div class='circle'></div> </div><div class='gap-patch'> <div class='circle'></div> </div><div class='circle-clipper right'> <div class='circle'></div> </div> </div> </div>";
            
            var lessonName = document.getElementById("lessonName" + parameter).value;
            $.ajax({url: "ajax/create.php?e=" + parameter + "&q=" + lessonName + "&m=" + idNotepad, success: function(result){
                try {
                    var arr = JSON.parse(result);
                    arr = arr.replace(/&#60;/g, "<");
                    arr = arr.replace(/&#62;/g, ">");
                    arr = arr.replace(/\\/g, '');
                    document.getElementById("containment").innerHTML = arr;
                    document.getElementById("fold-fab").style.display = "none";
                    $('.collapsible').collapsible();
                } catch (err) {
                    console.log("User tried to work with nonexistent items.");
                    window.location.reload();
                }
                
             }});
        }
    }
    
    function resetFields(parameter) {
        document.getElementById("lessonName" + parameter).value = "";
        Materialize.updateTextFields();
        idNotepad = "";
    }
    
    function resetFieldsAndOpen(referenceToSelf) {
        var cname = referenceToSelf.className;
        idNotepad = referenceToSelf.parentElement.parentElement.children[4].children[1].id;
        if (cname.indexOf("video") != -1) {
            document.getElementById("lessonNamev").value = "";
            $("#video").modal('open');
        } else {
            document.getElementById("lessonNamea").value = "";
            $("#activity").modal('open');
        }
    }
    
    function deleteRow(parameter) {
        var acceptDelete = confirm("Are you sure you want to delete this lesson? You won't be able to get it back!");
        if (acceptDelete) {
            typeParam = "";
            if (isStructured) {
                typeParam = "s";
            } else {
                typeParam = "u";
            }
            
            $.ajax({url: "ajax/delete.php?q=" + parameter + "&t=" + typeParam, success: function(result){
                try {
                    var arr = JSON.parse(result);
                    arr = arr.replace(/&#60;/g, "<");
                    arr = arr.replace(/&#62;/g, ">");
                    arr = arr.replace(/\\/g, '');
                    if (arr == "ERR_1") {
                        Materialize.toast("For your safety, we don't let you delete all of the lessons in a module. Please use the 'Delete Module' button instead.", 8000)
                        return;
                    }
                    
                    if (isStructured) {
                        document.getElementById("movable").innerHTML = arr;
                        Materialize.Toast.removeAll();
                    } else {
                        document.getElementById("containment").innerHTML = arr;
                        document.getElementById("fold-fab").style.display = "none";
                        $('.collapsible').collapsible();
                    }
                } catch (err) {
                    console.log(result);
                    console.log(err);
                    console.log("User tried to work with nonexistent items.");
                    window.location.reload();
                }
             
             }});
        }
    }
    
    function editModule(thing) {
        idNotepad = thing.id;
        var title = $("#" + thing.id).parent().children()[0].innerHTML;
        var description = $("#" + thing.id).parent().parent().parent().children()[1].children[1].innerHTML;
        
        document.getElementById("mdesc").value = description;
        document.getElementById("mname").value = title;
        Materialize.updateTextFields();
        $("#renameModal").modal('open');
        Materialize.updateTextFields();
    }
    
    function updateNames() {
        var description = document.getElementById("mdesc").value;
        var title = document.getElementById("mname").value;
        
        if (title == "" || description == "") {
            Materialize.toast("You forgot to fill something out! Please try again.", 5000);
        } else {
            $.ajax({url: "ajax/renameModule.php?d=" + description + "&t=" + title + "&m=" + idNotepad, success: function(result){
                try {
                    var arr = JSON.parse(result);
                    arr = arr.replace(/&#60;/g, "<");
                    arr = arr.replace(/&#62;/g, ">");
                    arr = arr.replace(/\\/g, '');
                    if (isStructured) {
                        document.getElementById("movable").innerHTML = arr;
                        Materialize.Toast.removeAll();
                    } else {
                        document.getElementById("containment").innerHTML = arr;
                        document.getElementById("fold-fab").style.display = "none";
                        $('.collapsible').collapsible();
                    }
                } catch (err) {
                    console.log("User tried to work with nonexistent items.");
                    window.location.reload();
                }
             
             }});
        }
    }
    
    function changeFoldState() {
        var modules = $("#containment").children()[0].children;
        
        if (open) {
            for (var i=0; i < modules.length; i++) {
                 $('.collapsible').collapsible('close', i);
            }
            
            document.getElementById("fold-icon").innerHTML = "unfold_more";
            open = false;
        } else {
            for (var i=0; i < modules.length; i++) {
                 $('.collapsible').collapsible('open', i);
            }
            
            document.getElementById("fold-icon").innerHTML = "unfold_less";
            open = true;
        }
    }
    
    function cancelSort() {
        window.location.reload();
    }
    
    function openCreateModule() {
        document.getElementById("moduleName").value = "";
        document.getElementById("lessonNamem").value = "";
        Materialize.updateTextFields();
        $("#module").modal('open');
        Materialize.updateTextFields();
    }
    
    function createModule() {
        var moduleName = document.getElementById("moduleName").value;
        var lessonName = document.getElementById("lessonNamem").value;
        if (moduleName == "" || lessonName == "") {
            Materialize.toast("Whoa! Be sure to fill everything out before submitting!", 5000);
        }
        
        $.ajax({url: "ajax/createModule.php?m=" + moduleName + "&v=" + lessonName, success: function(result){
                try {
                    var arr = JSON.parse(result);
                    arr = arr.replace(/&#60;/g, "<");
                    arr = arr.replace(/&#62;/g, ">");
                    arr = arr.replace(/\\/g, '');
                    document.getElementById("containment").innerHTML = arr;
                    document.getElementById("fold-fab").style.display = "none";
                    $('.collapsible').collapsible();
                } catch (err) {
                    console.log("User tried to work with nonexistent items.");
                    window.location.reload();
                }
             
        }});
    }
    

</script>

</body>
</html>