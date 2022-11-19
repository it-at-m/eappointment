import React, { useRef, useEffect } from 'react'
import qrGenerator from "qrcode-generator"
import PropTypes from 'prop-types'

const QrCodeComponent = props => {

    const canvasRef = useRef(null)

    const drawQrCode = (canvas, options) => {
        qrGenerator.stringToBytes = qrGenerator.stringToBytesFuncs['UTF-8']
        const codeObj = qrGenerator(options.version, options.ecLevel)
        codeObj.addData(options.text)
        codeObj.make()

        let ctx = canvas.getContext('2d')
        ctx.fillStyle = options.backgroundColor
        ctx.fillRect(0, 0, options.size, options.size)

        const gridSize = codeObj.getModuleCount()
        const blockSize = options.size / (gridSize + 2 * options.quiet)
        const offset = options.quiet * blockSize

        ctx.beginPath()
        ctx.fillStyle = options.fillColor
        for (let row = 0; row < gridSize; row += 1) {
            for (let col = 0; col < gridSize; col += 1) {
                let top = offset + row * blockSize
                let left = offset + col * blockSize

                if (codeObj.isDark(row, col)) {
                    ctx.rect(left, top, blockSize, blockSize)
                }
            }
        }

        ctx.fill()
    }
    
    const options = {
        version: 10,
        ecLevel: 'M',
        fillColor: '#000',
        backgroundColor: '#fff',
        quiet: 5,
        size: props.width,
        text: props.targetUrl
    }

    useEffect(() => {
        drawQrCode(canvasRef.current, options)
    }, [])
    
    return (<canvas ref={canvasRef} { ... props } />)
}

QrCodeComponent.propTypes = {
    targetUrl: PropTypes.string,
    width: PropTypes.string,
    height: PropTypes.string
}

export default QrCodeComponent