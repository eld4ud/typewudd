/**
 * Typewud 3D keyboard hero.
 * Three.js + locally created .glb, no React, no build step and no database changes.
 * Model is a real polygon-based glTF asset, not a CSS imitation.
 */
import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

const container = document.getElementById('typewudKeyboard3d');

if (container) {
    const status = document.getElementById('modelStatus');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let renderer;

    try {
        renderer = new THREE.WebGLRenderer({
            alpha: true,
            antialias: true,
            powerPreference: 'high-performance',
        });
    } catch (error) {
        if (status) {
            status.textContent = '3D tidak didukung oleh browser ini.';
        }
        console.warn('Typewud: WebGL unavailable', error);
    }

    if (renderer) {
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.8));
        renderer.setClearColor(0x000000, 0);
        renderer.outputColorSpace = THREE.SRGBColorSpace;
        renderer.toneMapping = THREE.ACESFilmicToneMapping;
        renderer.toneMappingExposure = 1.22;
        renderer.domElement.className = 'home-hero__canvas';
        renderer.domElement.setAttribute('aria-hidden', 'true');
        container.prepend(renderer.domElement);

        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(34, 1, 0.1, 100);
        camera.position.set(0, 5.8, 16.5);

        const ambient = new THREE.HemisphereLight(0xf4f4ff, 0x15161e, 2.1);
        scene.add(ambient);

        function directional(color, intensity, x, y, z) {
            const lamp = new THREE.DirectionalLight(color, intensity);
            lamp.position.set(x, y, z);
            scene.add(lamp);
        }

        directional(0xffffff, 4.8, -4, 11, 8);
        directional(0xa9b5c5, 3.0, 7, 5, -5);
        directional(0xbfc2cb, 1.6, 2, -2, 8);

        const modelPivot = new THREE.Group();
        scene.add(modelPivot);

        const controls = new OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.dampingFactor = 0.065;
        controls.enablePan = false;
        controls.enableZoom = false; // Avoid trapping normal page scrolling.
        controls.minPolarAngle = 0.28;
        controls.maxPolarAngle = 1.53;
        controls.autoRotate = !reducedMotion.matches;
        controls.autoRotateSpeed = 0.56;
        controls.target.set(0, 0, 0);
        controls.update();

        let resumeTimer;
        controls.addEventListener('start', () => {
            controls.autoRotate = false;
            window.clearTimeout(resumeTimer);
        });
        controls.addEventListener('end', () => {
            window.clearTimeout(resumeTimer);
            if (!reducedMotion.matches) {
                resumeTimer = window.setTimeout(() => {
                    controls.autoRotate = true;
                }, 4500);
            }
        });

        const modelUrl = container.dataset.model;
        new GLTFLoader().load(
            modelUrl,
            (gltf) => {
                const keyboard = gltf.scene;
                const box = new THREE.Box3().setFromObject(keyboard);
                const size = box.getSize(new THREE.Vector3());
                const center = box.getCenter(new THREE.Vector3());

                // Normalize varying model exports to the same framing.
                const scale = 9.4 / Math.max(size.x, size.y, size.z);
                keyboard.scale.setScalar(scale);
                keyboard.position.copy(center).multiplyScalar(-scale);
                modelPivot.add(keyboard);
                modelPivot.rotation.y = -0.34;
                modelPivot.rotation.z = -0.06;

                if (status) {
                    status.hidden = true;
                }
                container.classList.add('is-ready');
            },
            undefined,
            (error) => {
                if (status) {
                    status.textContent = 'Model keyboard gagal dimuat. Periksa koneksi internet dan file .glb.';
                }
                console.error('Typewud GLB load failed:', error);
            }
        );

        const clock = new THREE.Clock();
        let visible = true;
        let lastWidth = 0;
        let lastHeight = 0;

        function resize() {
            const width = Math.max(1, container.clientWidth);
            const height = Math.max(1, container.clientHeight);
            if (width === lastWidth && height === lastHeight) return;

            lastWidth = width;
            lastHeight = height;
            camera.aspect = width / height;
            camera.fov = width < 460 ? 44 : 34;
            camera.updateProjectionMatrix();
            renderer.setSize(width, height, false);
        }

        const observer = new IntersectionObserver(([entry]) => {
            visible = entry.isIntersecting;
        }, { threshold: 0.02 });
        observer.observe(container);

        const resizeObserver = new ResizeObserver(resize);
        resizeObserver.observe(container);
        resize();

        reducedMotion.addEventListener('change', () => {
            controls.autoRotate = !reducedMotion.matches;
        });

        function animate() {
            window.requestAnimationFrame(animate);
            if (document.hidden || !visible) return;

            const t = clock.getElapsedTime();
            if (!reducedMotion.matches) {
                modelPivot.position.y = Math.sin(t * 0.75) * 0.075;
                modelPivot.rotation.z = -0.06 + Math.sin(t * 0.42) * 0.025;
            }

            controls.update();
            renderer.render(scene, camera);
        }

        animate();
    }
}
