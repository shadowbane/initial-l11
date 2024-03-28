import '../css/app.css';
import './bootstrap.js';
import _ from "lodash";
import {ray} from 'node-ray/web'

window._ = _;
window.ray = ray;

/**
 * @type {{getAndReplace: Window.system.getAndReplace, appendOptions: Window.system.appendOptions, ready: Window.system.ready, json: (function(*): *), error: Window.system.error, getAndAppend: Window.system.getAndAppend, status: ((function(*=): (Promise<*>))|*), ml: ((function(*=, *=, *=): (*))|*), nester: (function(*, *=): *)}}
 */
window.system = {
    ready: (callback) => {
        if (document.readyState !== "loading") {
            callback();
        } else {
            document.addEventListener("DOMContentLoaded", callback);
        }
    },

    status: function (response) {
        if (response.status >= 200 && response.status < 300) {
            return Promise.resolve(response);
        } else {
            return response.json()   // response.json returns a promise, we chose to do nothing with its
                .then((json) => { // conclusion
                    let $error = json.exception;
                    if (json.message !== "") {
                        if (typeof json.data !== "undefined") {
                            if (json.data.message !== "") {
                                $error = json.data.message;
                            } else {
                                $error = json.message;
                            }
                        } else {
                            $error = json.message;
                        }
                    } else {
                        $error = json.message;
                    }
                    throw new Error($error);
                })
                .catch((error) => {
                    return Promise.reject(error);
                });
        }
    },

    json: function (response) {
        return response.json();
    },

    error: function (error) {
        swal({title: "Server Error", text: error.toString(), icon: 'error'});
    },

    appendOptions: (data, target) => {
        $("[name='" + target + "']").append($('<option>', {
            value: '',
            text: '-'
        }));
        $.each(data, function (key, val) {
            $("[name='" + target + "']").append($('<option>', {
                value: key,
                text: val
            }));
        });
    },

    getAndAppend: (source, target, key, url) => {
        $("[name='" + target + "'] option").each(function () {
            $(this).remove();
        });

        $.get('/' + url + '/find?' + key + '=' + $("[name='" + source + "']").val())
            .done(function (result) {
                system.appendOptions(result, target);
            });
    },

    getAndReplace: (source, target, key, url) => {
        $.get('/' + url + '/find?' + key + '=' + $("[name='" + source + "']").val())
            .done(function (result) {
                $.each(result.tests, function (index, val) {
                    $('#test_' + val).val(0);
                });

                $.each(target, function (index, val) {
                    $("#" + val).val(result[val]);
                });

                $.each(result.result, function (index, val) {
                    $('#test_' + index).val(val.score);
                })
            });
    },

    /**
     * @source https://idiallo.com/javascript/create-dom-elements-faster
     * @source https://github.com/ibudiallo/jml
     *
     * @param tagName
     * @param props
     * @param nest
     */
    ml: (tagName, props, nest) => {
        var el = document.createElement(tagName);
        if (props) {
            for (var name in props) {
                if (name.indexOf("on") === 0) {
                    el.addEventListener(name.substr(2).toLowerCase(), props[name], false)
                } else {
                    el.setAttribute(name, props[name]);
                }
            }
        }
        if (!nest) {
            return el;
        }
        return system.nester(el, nest)
    },

    /**
     * @source https://idiallo.com/javascript/create-dom-elements-faster
     * @source https://github.com/ibudiallo/jml
     *
     * @param el
     * @param n
     * @returns {*}
     */
    nester: (el, n) => {
        if (typeof n === "string") {
            var t = document.createTextNode(n);
            el.appendChild(t);
        } else if (n instanceof Array) {
            for (var i = 0; i < n.length; i++) {
                if (typeof n[i] === "string") {
                    var t1 = document.createTextNode(n[i]);
                    el.appendChild(t1);
                } else if (n[i] instanceof Node) {
                    el.appendChild(n[i]);
                }
            }
        } else if (n instanceof Node) {
            el.appendChild(n)
        }
        return el;
    },

};
