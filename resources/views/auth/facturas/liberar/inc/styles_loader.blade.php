<style>
    .loader-licenciador {
        width: 175px;
        height: 80px;
        display: block;
        margin: auto;
        background-image: radial-gradient(circle 25px at 25px 25px, #FFF 100%, transparent 0), radial-gradient(circle 50px at 50px 50px, #FFF 100%, transparent 0), radial-gradient(circle 25px at 25px 25px, #FFF 100%, transparent 0), linear-gradient(#FFF 50px, transparent 0);
        background-size: 50px 50px, 100px 76px, 50px 50px, 120px 40px;
        background-position: 0px 30px, 37px 0px, 122px 30px, 25px 40px;
        background-repeat: no-repeat;
        position: relative;
        box-sizing: border-box;
    }

    .loader-licenciador::after {
        content: '';
        left: 0;
        right: 0;
        margin: auto;
        bottom: 20px;
        position: absolute;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 5px solid transparent;
        border-color: #FF3D00 transparent;
        box-sizing: border-box;
        animation: rotation-licenciador 1s linear infinite;
    }

    @keyframes rotation-licenciador {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .contenedor-loader {
        display: flex;
        justify-content: center;
        align-items: center;
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 100%;
        background: rgba(0, 0, 0, 0.6);
        z-index: 99999;
    }
</style>
<div class="contenedor-loader d-none" id="spinnerLicenciador">
    <span class="loader-licenciador"></span>
</div>
