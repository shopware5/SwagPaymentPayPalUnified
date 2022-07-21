export default (function (){
    return {
        /**
         * @param {string} infoText
         */
        readThreeDSecureToken: function (infoText) {
            return /.*OTP: (\d{4,}).*/g.exec(infoText)[1];
        },
    }
}());
