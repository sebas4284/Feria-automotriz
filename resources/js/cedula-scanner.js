import { BrowserMultiFormatReader } from '@zxing/browser';
import { BarcodeFormat, DecodeHintType } from '@zxing/library';

export { BarcodeFormat };

const FORMATOS_SOPORTADOS = [BarcodeFormat.PDF_417, BarcodeFormat.CODE_39, BarcodeFormat.CODE_128];

export async function iniciarEscaneoCedula(videoElement, alCapturar, alFallar) {
    const hints = new Map();
    hints.set(DecodeHintType.POSSIBLE_FORMATS, FORMATOS_SOPORTADOS);

    const lector = new BrowserMultiFormatReader(hints);

    return lector.decodeFromVideoDevice(undefined, videoElement, (resultado, error) => {
        if (resultado) {
            alCapturar(resultado.getText(), resultado.getBarcodeFormat());
        } else if (error && error.name !== 'NotFoundException' && alFallar) {
            alFallar(error);
        }
    });
}

/**
 * Interpreta el texto crudo leído según el formato del código:
 * - PDF417 (cédula digital nueva): trae varios campos (apellidos,
 *   nombres, número de documento) separados por líneas. El orden
 *   exacto no está documentado de forma confiable, así que esto es
 *   un mejor esfuerzo: el resultado siempre debe mostrarse editable,
 *   nunca guardarse directo sin que la persona lo revise.
 * - Code 39 / Code 128 (cédula laminada antigua): el código solo trae
 *   el número de cédula, sin nombre.
 */
export function parsearCedula(textoCrudo, formato) {
    if (formato === BarcodeFormat.PDF_417) {
        const partes = textoCrudo.split(/[\r\n]+/).map(p => p.trim()).filter(Boolean);

        return {
            formato: 'nueva',
            identificacion: partes[2] ?? '',
            apellidos: [partes[0], partes[1]].filter(Boolean).join(' '),
            nombres: [partes[3], partes[4]].filter(Boolean).join(' '),
            textoCrudo,
        };
    }

    return {
        formato: 'antigua',
        identificacion: textoCrudo.replace(/\D/g, ''),
        apellidos: '',
        nombres: '',
        textoCrudo,
    };
}
