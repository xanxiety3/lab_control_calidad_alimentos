document.addEventListener("DOMContentLoaded", () => {

    // 🧫 Microbiología
    const microBtn = document.getElementById("btnCalcularMicro");
    if (microBtn) {
        microBtn.addEventListener("click", () => {
            const d1c1 = parseFloat(document.getElementById("dilucion1_c1").value) || 0;
            const d1c2 = parseFloat(document.getElementById("dilucion1_c2").value) || 0;
            const d2c1 = parseFloat(document.getElementById("dilucion2_c1").value) || 0;
            const d2c2 = parseFloat(document.getElementById("dilucion2_c2").value) || 0;

            const resultado = ((d1c1 + d1c2 + d2c1 + d2c2) / 4) * 1000;
            document.getElementById("resultadoMicro").textContent = `${resultado.toFixed(2)} ufc`;
            document.getElementById("resultado").value = resultado.toFixed(2);
        });
    }

    // ⚗️ Grasa
    const grasaBtn = document.getElementById("btnCalcularGrasa");
    if (grasaBtn) {
        grasaBtn.addEventListener("click", () => {
            const r1a = parseFloat(document.getElementById("r1_a").value) || 0;
            const r1b = parseFloat(document.getElementById("r1_b").value) || 0;
            const r2a = parseFloat(document.getElementById("r2_a").value) || 0;
            const r2b = parseFloat(document.getElementById("r2_b").value) || 0;

            const r1 = r1b - r1a;
            const r2 = r2b - r2a;
            const promedio = (r1 + r2) / 2;

            document.getElementById("resultadoGrasa").textContent = `${promedio.toFixed(2)} g/100g`;
            document.getElementById("resultado").value = promedio.toFixed(2);
        });
    }

    // 💧 Sólidos totales / humedad
    const porcentajeBtn = document.getElementById("btnCalcularPorcentaje");
    if (porcentajeBtn) {
        porcentajeBtn.addEventListener("click", () => {
            const m0 = parseFloat(document.getElementById("m0").value) || 0;
            const m1 = parseFloat(document.getElementById("m1").value) || 0;
            const m2 = parseFloat(document.getElementById("m2").value) || 0;

            const denom = m1 - m0;
            if (denom === 0) return alert("Error: m1 - m0 no puede ser 0");

            const resultado = ((m2 - m0) / denom) * 100;
            document.getElementById("resultadoPorcentaje").textContent = `${resultado.toFixed(2)} %`;
            document.getElementById("resultado").value = resultado.toFixed(2);
        });
    }
});
