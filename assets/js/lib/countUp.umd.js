"use strict";function _typeof(t){return(_typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}!function(t){"object"==("undefined"==typeof exports?"undefined":_typeof(exports))&&"undefined"!=typeof module?t(exports):"function"==typeof define&&define.amd?define(["exports"],t):t(("undefined"!=typeof globalThis?globalThis:self).countUp={})}(function(t){var e=function(){return(e=Object.assign||function(t){for(var i,n=1,e=arguments.length;n<e;n++)for(var s in i=arguments[n])Object.prototype.hasOwnProperty.call(i,s)&&(t[s]=i[s]);return t}).apply(this,arguments)};function i(t,i,n){var l=this;this.endVal=i,this.options=n,this.version="2.8.0",this.defaults={startVal:0,decimalPlaces:0,duration:2,useEasing:!0,useGrouping:!0,useIndianSeparators:!1,smartEasingThreshold:999,smartEasingAmount:333,separator:",",decimal:".",prefix:"",suffix:"",enableScrollSpy:!1,scrollSpyDelay:200,scrollSpyOnce:!1},this.finalEndVal=null,this.useEasing=!0,this.countDown=!1,this.error="",this.startVal=0,this.paused=!0,this.once=!1,this.count=function(t){l.startTime||(l.startTime=t);var t=t-l.startTime,i=(l.remaining=l.duration-t,l.useEasing?l.countDown?l.frameVal=l.startVal-l.easingFn(t,0,l.startVal-l.endVal,l.duration):l.frameVal=l.easingFn(t,l.startVal,l.endVal-l.startVal,l.duration):l.frameVal=l.startVal+(l.endVal-l.startVal)*(t/l.duration),l.countDown?l.frameVal<l.endVal:l.frameVal>l.endVal);l.frameVal=i?l.endVal:l.frameVal,l.frameVal=Number(l.frameVal.toFixed(l.options.decimalPlaces)),l.printValue(l.frameVal),t<l.duration?l.rAF=requestAnimationFrame(l.count):null!==l.finalEndVal?l.update(l.finalEndVal):l.options.onCompleteCallback&&l.options.onCompleteCallback()},this.formatNumber=function(t){var i=t<0?"-":"",t=Math.abs(t).toFixed(l.options.decimalPlaces),t=(t+="").split("."),n=t[0],t=1<t.length?l.options.decimal+t[1]:"";if(l.options.useGrouping){for(var e="",s=3,a=0,o=0,r=n.length;o<r;++o)l.options.useIndianSeparators&&4===o&&(s=2,a=1),0!==o&&a%s==0&&(e=l.options.separator+e),a++,e=n[r-o-1]+e;n=e}return l.options.numerals&&l.options.numerals.length&&(n=n.replace(/[0-9]/g,function(t){return l.options.numerals[+t]}),t=t.replace(/[0-9]/g,function(t){return l.options.numerals[+t]})),i+l.options.prefix+n+t+l.options.suffix},this.easeOutExpo=function(t,i,n,e){return n*(1-Math.pow(2,-10*t/e))*1024/1023+i},this.options=e(e({},this.defaults),n),this.formattingFn=this.options.formattingFn||this.formatNumber,this.easingFn=this.options.easingFn||this.easeOutExpo,this.startVal=this.validateValue(this.options.startVal),this.frameVal=this.startVal,this.endVal=this.validateValue(i),this.options.decimalPlaces=Math.max(this.options.decimalPlaces),this.resetDuration(),this.options.separator=String(this.options.separator),this.useEasing=this.options.useEasing,""===this.options.separator&&(this.options.useGrouping=!1),this.el="string"==typeof t?document.getElementById(t):t,this.el?this.printValue(this.startVal):this.error="[CountUp] target is null or undefined","undefined"!=typeof window&&this.options.enableScrollSpy&&(this.error?console.error(this.error,t):(window.onScrollFns=window.onScrollFns||[],window.onScrollFns.push(function(){return l.handleScroll(l)}),window.onscroll=function(){window.onScrollFns.forEach(function(t){return t()})},this.handleScroll(this)))}i.prototype.handleScroll=function(t){var i,n,e;t&&window&&!t.once&&(i=window.innerHeight+window.scrollY,n=(e=t.el.getBoundingClientRect()).top+window.pageYOffset,(e=e.top+e.height+window.pageYOffset)<i&&e>window.scrollY&&t.paused?(t.paused=!1,setTimeout(function(){return t.start()},t.options.scrollSpyDelay),t.options.scrollSpyOnce&&(t.once=!0)):(window.scrollY>e||i<n)&&!t.paused&&t.reset())},i.prototype.determineDirectionAndSmartEasing=function(){var t=this.finalEndVal||this.endVal,i=(this.countDown=this.startVal>t,t-this.startVal);Math.abs(i)>this.options.smartEasingThreshold&&this.options.useEasing?(this.finalEndVal=t,i=this.countDown?1:-1,this.endVal=t+i*this.options.smartEasingAmount,this.duration=this.duration/2):(this.endVal=t,this.finalEndVal=null),null!==this.finalEndVal?this.useEasing=!1:this.useEasing=this.options.useEasing},i.prototype.start=function(t){this.error||(this.options.onStartCallback&&this.options.onStartCallback(),t&&(this.options.onCompleteCallback=t),0<this.duration?(this.determineDirectionAndSmartEasing(),this.paused=!1,this.rAF=requestAnimationFrame(this.count)):this.printValue(this.endVal))},i.prototype.pauseResume=function(){this.paused?(this.startTime=null,this.duration=this.remaining,this.startVal=this.frameVal,this.determineDirectionAndSmartEasing(),this.rAF=requestAnimationFrame(this.count)):cancelAnimationFrame(this.rAF),this.paused=!this.paused},i.prototype.reset=function(){cancelAnimationFrame(this.rAF),this.paused=!0,this.resetDuration(),this.startVal=this.validateValue(this.options.startVal),this.frameVal=this.startVal,this.printValue(this.startVal)},i.prototype.update=function(t){cancelAnimationFrame(this.rAF),this.startTime=null,this.endVal=this.validateValue(t),this.endVal!==this.frameVal&&(this.startVal=this.frameVal,null==this.finalEndVal&&this.resetDuration(),this.finalEndVal=null,this.determineDirectionAndSmartEasing(),this.rAF=requestAnimationFrame(this.count))},i.prototype.printValue=function(t){var i;this.el&&(t=this.formattingFn(t),null!=(i=this.options.plugin)&&i.render?this.options.plugin.render(this.el,t):"INPUT"===this.el.tagName?this.el.value=t:"text"===this.el.tagName||"tspan"===this.el.tagName?this.el.textContent=t:this.el.innerHTML=t)},i.prototype.ensureNumber=function(t){return"number"==typeof t&&!isNaN(t)},i.prototype.validateValue=function(t){var i=Number(t);return this.ensureNumber(i)?i:(this.error="[CountUp] invalid start or end value: ".concat(t),null)},i.prototype.resetDuration=function(){this.startTime=null,this.duration=1e3*Number(this.options.duration),this.remaining=this.duration},t.CountUp=i,Object.defineProperty(t,"__esModule",{value:!0})});