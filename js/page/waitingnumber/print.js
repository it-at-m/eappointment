/* global window */
import $ from 'jquery'
import { getUrlParameters } from '../../lib/utils'

export default () => {
  $(document).ready(function () {
    const hasWaitingNumber = $('.msg_ihre_wartenummer')
    if (hasWaitingNumber.length > 0) {
        if (getUrlParameters().print === "1") {
            setTimeout(() => {
                window.print();
                window.onfocus=function(){
                    window.close();
                }
            }, 500);
        }
    }
  });
}
