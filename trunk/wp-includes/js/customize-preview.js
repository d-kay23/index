(function(a,c){var b=wp.customize;b.Preview=b.Messenger.extend({initialize:function(f,e){var d=this;b.Messenger.prototype.initialize.call(this,f,null,e);this.body=c(document.body);this.body.on("click.preview","a",function(g){g.preventDefault();d.send("url",c(this).attr("href"))});this.body.on("submit.preview","form",function(g){g.preventDefault()})}});c(function(){b.settings=window._wpCustomizeSettings;if(!b.settings){return}var e,d;e=new b.Preview(window.location.href);c.each(b.settings.values,function(g,f){b.set(g,f)});e.bind("setting",function(f){b.set.apply(b,f)});d=c(document.body);b("background_color",function(f){f.bind(function(g){d.css("background-color",g?"#"+g:"")})})})})(wp,jQuery);