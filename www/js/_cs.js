(function(){
	if (typeof(window.cs_url)=='undefined') window.cs_url = '/';
	window.cs = function (a,b ){
		if (typeof(arguments[0]) == 'undefined') arguments[0] = '';
		if (typeof(arguments[1]) == 'undefined') arguments[1] = {};
		function status(response) {
			if (response.status >= 200 && response.status < 300) {
				return Promise.resolve(response)
			} else {
				return Promise.reject(new Error(response.statusText))
			}
		}
		function json(response) {
			//if (response.headers.get("content-type").indexOf("application/json") !== -1) { 
				return response.text().then(function(data){
					//console.log(arguments)
					if (response.headers.get("content-type").indexOf("application/json") !== -1) {
						try{
							data = JSON.parse(data)
						}catch(err){
							return data
						}
					}
					if (((typeof(data.success) != 'undefined') && (data.success == true))
						&& (
							 ((typeof(data.resptype) != 'undefined') && (data.resptype == 'rs'))
							)
						){
						var id = a.replace("/","_")
						if ($("#" + id).length > 0) $("#" + id).remove()
						$("body").append("<div id='" + id + "'>" + data.resp.html + "</div>")
					}
					return data
				})
			//}
			//return response.text()
		}
		
		var options = {
			credentials: "include",
			method:'post',
		}
		
		if ((typeof(b) == 'object') && (!(b instanceof FormData)) && (!(b instanceof File))){
			options.headers = new Headers({'Content-Type': 'application/json'})
			b = JSON.stringify(b)
		}
		options.body = b
		return fetch(window.cs_url_po + 'csapi/' + a ,options)
		.then(status)
		.then(json)
		.catch(function(error) {
			console.log('Request failed', error);
			return Promise.reject(new Error(error))
		})
	}
}())
setURLParameter = function(param) {
	//var iparam					= {paramName, paramValue, url}
	//if paramValue == null -> remove from query
	var iparam					= {name:'', value:'', url:window.location.href}
	jQuery.extend(true,iparam,param)
	if (iparam.url.indexOf(iparam.name + "=") >= 0){
		var prefix = iparam.url.substring(0, iparam.url.indexOf(iparam.name));
		var suffix = iparam.url.substring(iparam.url.indexOf(iparam.name));
		suffix = suffix.substring(suffix.indexOf("=") + 1);
		suffix = (suffix.indexOf("&") >= 0) ? suffix.substring(suffix.indexOf("&")) : "";
		if (iparam.value == null){
			iparam.url = prefix.substring(0,prefix.length - 1) + suffix;				
		}else{
			iparam.url = prefix + iparam.name + "=" + encodeURIComponent(iparam.value) + suffix;				
		}
	}else{
		if (iparam.value != null){
			if (iparam.url.indexOf("?") < 0)
				iparam.url += "?" + iparam.name + "=" + encodeURIComponent(iparam.value);
			else
				iparam.url += "&" + iparam.name + "=" + encodeURIComponent(iparam.value);
		}
	}
	return iparam.url
}
getURLParameter = function(param) {
	//[url,name]
	var iparam					= {
		url:window.location, 
		name:''
	}
	jQuery.extend(true,iparam,param)
	return decodeURIComponent((new RegExp('[?|&]' + iparam.name + '=' + '([^&;]+?)(&|#|;|$)').exec(iparam.url.search)||[,""])[1].replace(/\+/g, '%20'))||null
}
Number.prototype.pad = function(size) {
  var sign = Math.sign(this) === -1 ? '-' : '';
  return sign + new Array(size).concat([Math.abs(this)]).join('0').slice(-size);
}
decodeHtmlspecialChars = function(text){
    var map = {
        '&amp;': '&',
        '&#038;': "&",
        '&lt;': '<',
        '&gt;': '>',
        '&quot;': '"',
        '&#039;': "'",
        '&#8217;': "’",
        '&#8216;': "‘",
        '&#8211;': "–",
        '&#8212;': "—",
        '&#8230;': "…",
        '&#8221;': '”'
    };

    return text.replace(/\&[\w\d\#]{2,5}\;/g, function(m) { return map[m]; });
}
function sqlToJsDate(sqlDate){
    //sqlDate in SQL DATETIME format ("yyyy-mm-dd hh:mm:ss.ms")
    var sqlDateArr1 = sqlDate.split("-");
    //format of sqlDateArr1[] = ['yyyy','mm','dd hh:mm:ms']
    var sYear = sqlDateArr1[0];
    var sMonth = (Number(sqlDateArr1[1]) - 1).toString();
    var sqlDateArr2 = sqlDateArr1[2].split(" ");
    //format of sqlDateArr2[] = ['dd', 'hh:mm:ss.ms']
    var sDay = sqlDateArr2[0];
	var sHour = 0;
	var sMinute = 0;
	var sSecond = 0;
    var sMillisecond = 0;
	if (sqlDateArr2.length > 1){
    var sqlDateArr3 = sqlDateArr2[1].split(":");
		//format of sqlDateArr3[] = ['hh','mm','ss.ms']
		sHour = sqlDateArr3[0];
		sMinute = sqlDateArr3[1];
		var sqlDateArr4 = sqlDateArr3[2].split(".");
		//format of sqlDateArr4[] = ['ss','ms']
		sSecond = sqlDateArr4[0];
		if (sqlDateArr4.length > 1)
		sMillisecond = sqlDateArr4[1];		
	}
    return new Date(sYear,sMonth,sDay,sHour,sMinute,sSecond,sMillisecond);
}
// https://tc39.github.io/ecma262/#sec-array.prototype.includes
if (!Array.prototype.includes) {
  Object.defineProperty(Array.prototype, 'includes', {
    value: function(searchElement, fromIndex) {

      if (this == null) {
        throw new TypeError('"this" is null or not defined');
      }

      // 1. Let O be ? ToObject(this value).
      var o = Object(this);

      // 2. Let len be ? ToLength(? Get(O, "length")).
      var len = o.length >>> 0;

      // 3. If len is 0, return false.
      if (len === 0) {
        return false;
      }

      // 4. Let n be ? ToInteger(fromIndex).
      //    (If fromIndex is undefined, this step produces the value 0.)
      var n = fromIndex | 0;

      // 5. If n ≥ 0, then
      //  a. Let k be n.
      // 6. Else n < 0,
      //  a. Let k be len + n.
      //  b. If k < 0, let k be 0.
      var k = Math.max(n >= 0 ? n : len - Math.abs(n), 0);

      function sameValueZero(x, y) {
        return x === y || (typeof x === 'number' && typeof y === 'number' && isNaN(x) && isNaN(y));
      }

      // 7. Repeat, while k < len
      while (k < len) {
        // a. Let elementK be the result of ? Get(O, ! ToString(k)).
        // b. If SameValueZero(searchElement, elementK) is true, return true.
        if (sameValueZero(o[k], searchElement)) {
          return true;
        }
        // c. Increase k by 1. 
        k++;
      }

      // 8. Return false
      return false;
    }
  });
}