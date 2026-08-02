import { BrowserPDF417Reader } from '@zxing/browser';

export async function iniciarEscaneoPdf417(videoElement, alCapturar, alFallar) {
    const lector = new BrowserPDF417Reader();

    return lector.decodeFromVideoDevice(undefined, videoElement, (resultado, error) => {
        if (resultado) {
            alCapturar(resultado.getText());
        } else if (error && error.name !== 'NotFoundException' && alFallar) {
            alFallar(error);
        }
    });
}

/**
 * Intenta separar el texto crudo del PDF417 de la cédula digital en
 * campos (apellidos, nombres, número de documento). El formato exacto
 * no está documentado de forma confiable, así que esto es un mejor
 * esfuerzo: el resultado siempre debe mostrarse editable, nunca
 * guardarse directo sin que la persona lo revise.
 */
export function parsearCedulaPdf417(textoCrudo) {
    const partes = textoCrudo.split(/[\r\n]+/).map(p => p.trim()).filter(Boolean);

    return {
        identificacion: partes[2] ?? '',
        apellidos: [partes[0], partes[1]].filter(Boolean).join(' '),
        nombres: [partes[3], partes[4]].filter(Boolean).join(' '),
        textoCrudo,
    };
}
