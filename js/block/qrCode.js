import BaseView from '../lib/baseview';
import $ from "jquery";
import qrGenerator from "qrcode-generator";

class View extends BaseView {

    constructor(element) {
        super(element);
        this.bindPublicMethods('initQrCode');
        console.log("Found qrCode");
        $(window).on(
            'load', () => {
                this.initQrCode();
            }
        );
    }

    initQrCode() {
        let options = window.bo.zmscalldisplay.qrCode;
        let instance = this;

        $('canvas.qrCode').each(function(index, element) {
            if (element.nodeName !== 'CANVAS') {
                return;
            }

            options.size = Math.min(element.offsetWidth, element.offsetHeight);
            options.text = window.location.origin + $(element).data('text');

            instance.drawQrCode(element, options);
        });
    }

    drawQrCode(canvas, options) {
        qrGenerator.stringToBytes = qrGenerator.stringToBytesFuncs['UTF-8'];
        const codeObj = qrGenerator(options.version, options.ecLevel);
        codeObj.addData(options.text);
        codeObj.make();

        let ctx = canvas.getContext('2d');
        ctx.fillStyle = options.backgroundColor;
        ctx.fillRect(0, 0, options.size, options.size);

        const gridSize  = codeObj.getModuleCount();
        const blockSize = options.size / (gridSize + 2 * options.quiet);
        const offset    = options.quiet * blockSize; // quiet zone

        ctx.beginPath();
        ctx.fillStyle = options.fillColor;
        for (let row = 0; row < gridSize; row += 1) {
            for (let col = 0; col < gridSize; col += 1) {
                let top  = offset + row * blockSize;
                let left = offset + col * blockSize;

                if (codeObj.isDark(row, col)) {
                    ctx.rect(left, top, blockSize, blockSize);
                }
            }
        }

        ctx.fill();
    }
}

export default View;
