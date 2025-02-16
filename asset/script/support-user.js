/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 2);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./script/support-user.js":
/*!********************************!*\
  !*** ./script/support-user.js ***!
  \********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function($) {var pageUrl = window.wp_dev_assist_support_user.page_url;
var shareNonce = window.wp_dev_assist_support_user.share_nonce;
var shareQueryKeys = window.wp_dev_assist_support_user.share_query_keys;
$(document).on('ready', function () {
  initCopy();
  initShare();
});
function initCopy() {
  var copiedTextTimeout;
  $('#da-copy-support-user-credentials').on('click', function () {
    var $this = $(this);
    var credentialsText = $('#da-support-user-credentials li').map(function () {
      return $(this).text().trimStart().trimEnd();
    }).get().join('\n');
    var $tempInput = $('<textarea>');
    $tempInput.appendTo('body').addClass('da-support-user__hidden-element').val(credentialsText).select();
    document.execCommand('copy');
    $tempInput.remove();
    $this.addClass('da-support-user__copy_copied');
    clearTimeout(copiedTextTimeout);
    copiedTextTimeout = setTimeout(function () {
      $this.removeClass('da-support-user__copy_copied');
    }, 2500);
  });
}
function initShare() {
  $('#da-share-support-user').on('click', function () {
    var $email = $('#da-share-support-user-email');
    var email = $email.val();
    var password = $('#da-support-user-password').text().trim();
    if ('' === email || !$email[0].reportValidity() || '' === password) {
      return;
    }
    var message = $('#da-share-support-user-message').val();
    var $tempForm = $('<form>');
    var $tempPasswordInput = $('<input>');
    var $tempMessageInput = $('<textarea>');
    var actionUrl = pageUrl + '&' + shareQueryKeys.email + '=' + email + '&' + '_wpnonce=' + shareNonce;
    $tempForm.appendTo('body').addClass('da-support-user__hidden-element').attr('method', 'post').attr('action', actionUrl).append($tempPasswordInput).append($tempMessageInput);
    $tempPasswordInput.attr('name', shareQueryKeys.password).val(password);
    $tempMessageInput.attr('name', shareQueryKeys.message).val(message);
    $tempForm.submit();
  });
}
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./style/support-user.scss":
/*!*********************************!*\
  !*** ./style/support-user.scss ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ 2:
/*!****************************************************************!*\
  !*** multi ./style/support-user.scss ./script/support-user.js ***!
  \****************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! ./style/support-user.scss */"./style/support-user.scss");
module.exports = __webpack_require__(/*! ./script/support-user.js */"./script/support-user.js");


/***/ }),

/***/ "jquery":
/*!*************************!*\
  !*** external "jQuery" ***!
  \*************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = jQuery;

/***/ })

/******/ });