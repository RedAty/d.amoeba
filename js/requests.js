/**
 * Frontend Core Object
 *
 * @file    requests.js
 * @author  Attila Reterics
 * @license GPL-3
 * @url     https://github.com/RedAty/d.amoeba
 * @date    2020. 11. 20.
 */

const Request = {
    /**
     *
     * @param {function} callback
     */
    ready: (callback) => {
        if (document.readyState !== "loading") callback();
        else document.addEventListener("DOMContentLoaded", callback);
    },
    /**
     * @param {object} json
     * @returns {string}
     */
    convertToFormEncoded: (json)=>{
        if(typeof json === "string"){
            return json;
        }
        if(typeof json === "number"){
            return json.toString();
        }
        if (json instanceof HTMLFormElement && json.tagName && json.tagName.toLowerCase() === "form") {
            json = new FormData(json);
        }
        if (json instanceof FormData){
            const formData = json;
            json = {};
            for (const [key, value] of formData.entries()) {
                json[key] = value;
            }
        }
        if(!json || typeof json != "object") {
            return "";
        }
        let uri = "";
        const keys = Object.keys(json);
        keys.forEach(key=>{
            const value = json[key];
            switch (typeof value) {
                case "string":
                    uri+="&"+key+"="+encodeURIComponent(value);
                    break;
                case "number":
                    uri+="&"+key+"="+encodeURIComponent(value);
                    break;
                case "object":
                    if(!value){
                        uri+="&"+key+"=";
                    }
                    break;
            }
        });
        return uri.substring(1);
    },
    /**
     * @param {HTMLFormElement} htmlFormElement
     * @returns {FormData}
     */
    getMultiPartForm: (htmlFormElement)=>{
        //multipart/form-data
        return new FormData(htmlFormElement);
    },
    /**
     *
     * @param {string} url
     * @param {object|FormData|HTMLFormElement|string} body
     * @param {function} callback
     * @returns {boolean}
     */
    post: (url, body, callback = ()=>{}) => {
        const xHTTP = new XMLHttpRequest();
        xHTTP.onreadystatechange = function () {
            // code
            if (this.readyState === 4 && this.status === 200) {
                callback(false, xHTTP.response, this.status);
            } else if (this.readyState === 4) {
                callback(true, xHTTP.response, this.status);
            }
        };
        xHTTP.open('POST', url);

        if (body instanceof FormData) {
            xHTTP.setRequestHeader('Content-Type', 'application/multipart/form-data')
            //xHTTP.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        } else if (typeof body === "string") {
            xHTTP.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
        } else if (typeof body === "object" && body) {
            xHTTP.setRequestHeader('Content-Type', 'application/json');
            body = JSON.stringify(body);
        } else if (body instanceof HTMLFormElement) {
            xHTTP.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            body = new FormData(body);
        } else {
            callback("Invalid input body");
            return false;
        }

        try {
            const requestBody = typeof body === "string" || body instanceof FormData ? body : body.toString();
            xHTTP.send(requestBody);
        } catch (e) {
            console.error(e);
            callback(e.message);
        }
    },
    /**
     * @param {string} url
     * @param {function} callback
     */
    get: (url, callback = ()=>{}) => {
        const xHTTP = new XMLHttpRequest();

        xHTTP.open('GET', url, true);

        xHTTP.onload = function () {
            callback(null, xHTTP.response)
        };
        try {
            xHTTP.send(null);
        } catch (e) {
            callback(e.message);
        }
        //xHTTP.send(null);
    },
    navigate: (url)=>{
        location.href = url;
    },
    startGame:function (url, querystring){
        const self = this;
        const body = querystring ? "start=true&" + querystring : "start=true";
        self.post("",body,function (error,data){
            if(!error){
                if(data){
                    const resultArray = data.split("-");
                    if(resultArray[0] === "ok"){
                        self.navigate(url);
                    } else {
                        console.error("Something bad happened");
                        console.log(resultArray);
                    }
                }
            } else {
                console.error(error);
            }
            //self.navigate(url);
        });
    },
    /**
     *
     * @param {{x:number, y:number}} coordinates
     * @param callback
     */
    makeMove: function(coordinates, callback = ()=>{}){
        if(coordinates && typeof coordinates === "object" && typeof coordinates.x === "number" && typeof coordinates.y === "number"){
            const body =  "x="+coordinates.x+"&y="+coordinates.y;
            const self = this;

            self.post("",body, callback);
        } else {
            if(!coordinates || typeof coordinates !== "object"){
                callback(new Error("Invalid Input: Coordinates is not an object"),null);
            } else if(typeof coordinates.x !== "number"){
                callback(new Error("Invalid Input: X coordinate is not an object. It is a "+typeof coordinates.x),null);
            } else if(typeof coordinates.y !== "number"){
                callback(new Error("Invalid Input: Y coordinate is not an object. It is a "+typeof coordinates.y),null);
            } else {
                callback(new Error("Invalid input: "+
                    "isObject: "+ (typeof coordinates === "object") +
                    "X is number: "+ (typeof coordinates.x === "number") +
                    "Y is number: "+ (typeof coordinates.y === "number")),null);
            }
        }
    },
    changeLanguage: function (language){
        if(language){
            this.post("","language="+language, (error,result)=>{
                if(!error && result){
                    location.reload();
                } else {
                    console.error(error);
                }
            });
        } else {
            console.error("Wrong input data");
        }
    }
};