!function(e){function t(e,t){t&&e.append(t.jquery?t.clone():t)}var n;e.fn.printThis=function(i){n=e.extend({},e.fn.printThis.defaults,i);var a=this instanceof jQuery?this:e(this),o="printThis-"+(new Date).getTime();if(window.location.hostname!==document.domain&&navigator.userAgent.match(/msie/i)){var r='javascript:document.write("<head><script>document.domain=\\"'+document.domain+'\\";<\/script></head><body></body>")',s=document.createElement("iframe");s.name="printIframe",s.id=o,s.className="MSIE",document.body.appendChild(s),s.src=r}else e("<iframe id='"+o+"' name='printIframe' />").appendTo("body");var c=e("#"+o);n.debug||c.css({position:"absolute",width:"0px",height:"0px",left:"-600px",top:"-600px"}),"function"==typeof n.beforePrint&&n.beforePrint(),setTimeout((function(){n.doctypeString&&function(e,t){var n,i;(i=(n=(n=e.get(0)).contentWindow||n.contentDocument||n).document||n.contentDocument||n).open(),i.write(t),i.close()}(c,n.doctypeString);var i,o=c.contents(),r=o.find("head"),s=o.find("body"),d=e("base");i=!0===n.base&&d.length>0?d.attr("href"):"string"==typeof n.base?n.base:document.location.protocol+"//"+document.location.host,r.append('<base href="'+i+'">'),n.importCSS&&e("link[rel=stylesheet]").each((function(){var t=e(this).attr("href");if(t){var n=e(this).attr("media")||"all";r.append("<link type='text/css' rel='stylesheet' href='"+t+"' media='"+n+"'>")}})),n.importStyle&&e("style").each((function(){r.append(this.outerHTML)})),n.pageTitle&&r.append("<title>"+n.pageTitle+"</title>"),n.loadCSS&&(e.isArray(n.loadCSS)?jQuery.each(n.loadCSS,(function(e,t){r.append("<link type='text/css' rel='stylesheet' href='"+this+"'>")})):r.append("<link type='text/css' rel='stylesheet' href='"+n.loadCSS+"'>"));var l=e("html")[0];o.find("html").prop("style",l.style.cssText);var p=n.copyTagClasses;if(p&&(-1!==(p=!0===p?"bh":p).indexOf("b")&&s.addClass(e("body")[0].className),-1!==p.indexOf("h")&&o.find("html").addClass(l.className)),(p=n.copyTagStyles)&&(-1!==(p=!0===p?"bh":p).indexOf("b")&&s.attr("style",e("body")[0].style.cssText),-1!==p.indexOf("h")&&o.find("html").attr("style",l.style.cssText)),t(s,n.header),n.canvas){var f=0;a.find("canvas").addBack("canvas").each((function(){e(this).attr("data-printthis",f++)}))}if(function(t,n,i){var a,o,r,s=n.clone(i.formValues);i.formValues&&(a=s,o="select, textarea",r=n.find(o),a.find(o).each((function(t,n){e(n).val(r.eq(t).val())}))),i.removeScripts&&s.find("script").remove(),i.printContainer?s.appendTo(t):s.each((function(){e(this).children().appendTo(t)}))}(s,a,n),n.canvas&&s.find("canvas").each((function(){var t=e(this).data("printthis"),n=e('[data-printthis="'+t+'"]');this.getContext("2d").drawImage(n[0],0,0),e.isFunction(e.fn.removeAttr)?n.removeAttr("data-printthis"):e.each(n,(function(e,t){t.removeAttribute("data-printthis")}))})),n.removeInline){var m=n.removeInlineSelector||"*";e.isFunction(e.removeAttr)?s.find(m).removeAttr("style"):s.find(m).attr("style","")}t(s,n.footer),function(e,t){var n=e.get(0);n=n.contentWindow||n.contentDocument||n,"function"==typeof t&&("matchMedia"in n?n.matchMedia("print").addListener((function(e){e.matches&&t()})):n.onbeforeprint=t)}(c,n.beforePrintEvent),setTimeout((function(){c.hasClass("MSIE")?(window.frames.printIframe.focus(),r.append("<script>  window.print(); <\/script>")):document.queryCommandSupported("print")?c[0].contentWindow.document.execCommand("print",!1,null):(c[0].contentWindow.focus(),c[0].contentWindow.print()),n.debug||setTimeout((function(){c.remove()}),1e3),"function"==typeof n.afterPrint&&n.afterPrint()}),n.printDelay)}),333)},e.fn.printThis.defaults={debug:!1,importCSS:!0,importStyle:!0,printContainer:!0,loadCSS:"",pageTitle:"",removeInline:!1,removeInlineSelector:"*",printDelay:1e3,header:null,footer:null,base:!1,formValues:!0,canvas:!0,doctypeString:"<!DOCTYPE html>",removeScripts:!1,copyTagClasses:!0,copyTagStyles:!0,beforePrintEvent:null,beforePrint:null,afterPrint:null}}(jQuery);