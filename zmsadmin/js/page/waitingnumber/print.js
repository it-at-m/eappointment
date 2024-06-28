import $ from 'jquery'
import { getUrlParameters } from '../../lib/utils'

export default () => {
  $(function () {
    const hasWaitingNumber = $('.print-number')
    const hasPrintMail = $('.print-mail')
    if (hasWaitingNumber.length > 0 || hasPrintMail) {
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
