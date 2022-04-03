function autocomplete(inp,server,callback) {
  /*the autocomplete function takes two arguments,
  the text field element and an array of possible autocompleted values:*/
  var currentFocus = 1;
  /*execute a function when someone writes in the text field:*/
	inp.addEventListener("input", function(e) {	  
		var a, b, i, val = this.value;
		this.removeAttribute("location_id")
		if (typeof(callback) == 'function') callback('');
		/*close any already open lists of autocompleted values*/
		closeAllLists();
		if (!val) { return false;}
		var that = this
		synced(server + val).then(function(resp) {
			var resp = JSON.parse(resp)
			if ((typeof(resp.success) == 'undefined')||(resp.success != true)) return false;
			resp = resp.resp.rows
			currentFocus = -1;
			/*create a DIV element that will contain the items (values):*/
			a = document.createElement("DIV");
			a.setAttribute("id", that.id + "autocomplete-list");
			a.setAttribute("class", "autocomplete-items");
			/*append the DIV element as a child of the autocomplete container:*/
			that.parentNode.appendChild(a);
			/*for each item in the array...*/
			for (i = 0; i < resp.length; i++) {
				/*check if the item starts with the same letters as the text field value:*/
				if (resp[i].parentname != null){
					resp[i].denumire += " [" + resp[i].parentname + "]";						
				}
				var index = resp[i].denumire.toUpperCase().indexOf(val.toUpperCase())
				if (index !== -1) {
					/*create a DIV element for each matching element:*/
					b = document.createElement("DIV");
					/*make the matching letters bold:*/
					b.innerHTML = "" + resp[i].denumire.substr(0, index);
					b.innerHTML += "<strong>" + resp[i].denumire.substr(index, val.length) + "</strong>";
					b.innerHTML += resp[i].denumire.substr(index+val.length);
					/*insert a input field that will hold the current array item's value:*/
					b.innerHTML += "<input type='hidden' value='" + resp[i].denumire + "' location_id='" + resp[i].id + "'>";
					/*execute a function when someone clicks on the item value (DIV element):*/
					b.addEventListener("click", function(e) {
						/*insert the value for the autocomplete text field:*/
						var selectedinp = this.getElementsByTagName("input")[0]
						//console.log('bar')
						//console.log(selectedinp)
						inp.value = selectedinp.value;
						var inpid = selectedinp.getAttribute("location_id")
						that.setAttribute("location_id", inpid);
						if (typeof(callback) == 'function') callback(inpid);
						/*close the list of autocompleted values,
						(or any other open lists of autocompleted values:*/
						closeAllLists();
					});
					a.appendChild(b);
				}
			}
			  var x = document.getElementById(that.id + "autocomplete-list");
			  if (x) x = x.getElementsByTagName("div");
			  if ((x !== null) && (x.length > 0)){
				  if (currentFocus < 0){
					  currentFocus = 0
					  addActive(x);
				  }
			  }
		}).catch(function(error) {
			console.log('Request failed', error);
		});
	});
	inp.addEventListener("focusout", function(e){
		var that = this
		setTimeout(function(){
			//console.log('foo')
			//console.log(that)
			//console.log(currentFocus)
			var x = document.getElementById(that.id + "autocomplete-list");
			if (x) x = x.getElementsByTagName("div");
			//console.log(x)
			//console.log(currentFocus)
			if ((x == null)||(x.length == 0)) {
				var inpid = that.getAttribute("location_id");
				if (typeof(callback) == 'function') callback(inpid);
				return
			}
			if (currentFocus < 0) {
				currentFocus = 0
				addActive(x);
			}
			if (x) x[currentFocus].click();
		},500)

	});
  /*execute a function presses a key on the keyboard:*/
  inp.addEventListener("keydown", function(e) {
      var x = document.getElementById(this.id + "autocomplete-list");
      if (x) x = x.getElementsByTagName("div");
      if (e.keyCode == 40) {
        /*If the arrow DOWN key is pressed,
        increase the currentFocus variable:*/
        currentFocus++;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 38) { //up
        /*If the arrow UP key is pressed,
        decrease the currentFocus variable:*/
        currentFocus--;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 13) {
        /*If the ENTER key is pressed, prevent the form from being submitted,*/
        e.preventDefault();
        if (currentFocus > -1) {
          /*and simulate a click on the "active" item:*/
          if (x) x[currentFocus].click();
        }
      }
  });
  function addActive(x) {
    /*a function to classify an item as "active":*/
    if (!x) return false;
    /*start by removing the "active" class on all items:*/
    removeActive(x);
    if (currentFocus >= x.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = (x.length - 1);
    /*add class "autocomplete-active":*/
    x[currentFocus].classList.add("autocomplete-active");
	x[currentFocus].scrollIntoView({block: "end"});
  }
  function removeActive(x) {
    /*a function to remove the "active" class from all autocomplete items:*/
    for (var i = 0; i < x.length; i++) {
      x[i].classList.remove("autocomplete-active");
    }
  }
  function closeAllLists(elmnt) {
    /*close all autocomplete lists in the document,
    except the one passed as an argument:*/
    var x = document.getElementsByClassName("autocomplete-items");
    for (var i = 0; i < x.length; i++) {
      if (elmnt != x[i] && elmnt != inp) {
        x[i].parentNode.removeChild(x[i]);
      }
    }
  }
  /*execute a function when someone clicks in the document:*/
  document.addEventListener("click", function (e) {
		closeAllLists(e.target);
      });
	function getWithCancel(url, token) { // the token is for cancellation
		var xhr = new XMLHttpRequest;
		xhr.open("GET", url);
		xhr.send();
		return new Promise(function(resolve, reject) {
			xhr.onload = function() { resolve(xhr.responseText);};
			token.cancel = function() {  // SPECIFY CANCELLATION
				xhr.abort(); // abort request
				reject("Cancelled"); // reject the promise
			};
			xhr.onerror = reject;
		});
	};
	function last(fn) {
		var lastToken = { cancel: function(){} }; // start with no op
		return function() {
			lastToken.cancel();
			var args = Array.prototype.slice.call(arguments);
			args.push(lastToken);
			return fn.apply(this, args);
		};
	}
	var synced = last(getWithCancel);
}
