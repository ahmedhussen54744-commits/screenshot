/**
 * TMV 3D Effects - Three.js Background Particles
 * Advanced 3D visual effects for the Trademark Verification System
 */
(function() {
    'use strict';

    // Only initialize on verify and apply pages
    var container = document.querySelector('.tmv-verify-container, .tmv-apply-container');
    if (!container) return;

    // Check if Three.js is available
    if (typeof THREE === 'undefined') return;

    var scene, camera, renderer, particles;
    var mouseX = 0, mouseY = 0;
    var windowHalfX = window.innerWidth / 2;
    var windowHalfY = window.innerHeight / 2;

    function init() {
        // Create canvas container
        var canvasContainer = document.createElement('div');
        canvasContainer.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:0;opacity:0.4;';
        canvasContainer.id = 'tmv-3d-canvas';
        document.body.insertBefore(canvasContainer, document.body.firstChild);

        // Scene
        scene = new THREE.Scene();
        
        // Camera
        camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 1, 2000);
        camera.position.z = 500;

        // Particles
        var geometry = new THREE.BufferGeometry();
        var vertices = [];
        var colors = [];

        var color1 = new THREE.Color(0x1a5c3a); // Green
        var color2 = new THREE.Color(0xffd700); // Gold
        var color3 = new THREE.Color(0x2d8a5e); // Light Green

        for (var i = 0; i < 800; i++) {
            var x = Math.random() * 2000 - 1000;
            var y = Math.random() * 2000 - 1000;
            var z = Math.random() * 2000 - 1000;
            vertices.push(x, y, z);

            var colorChoice = Math.random();
            var c;
            if (colorChoice < 0.33) c = color1;
            else if (colorChoice < 0.66) c = color2;
            else c = color3;
            
            colors.push(c.r, c.g, c.b);
        }

        geometry.setAttribute('position', new THREE.Float32BufferAttribute(vertices, 3));
        geometry.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));

        var material = new THREE.PointsMaterial({
            size: 3,
            vertexColors: true,
            transparent: true,
            opacity: 0.6,
            blending: THREE.AdditiveBlending
        });

        particles = new THREE.Points(geometry, material);
        scene.add(particles);

        // Renderer
        renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
        renderer.setPixelRatio(window.devicePixelRatio);
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setClearColor(0x000000, 0);
        canvasContainer.appendChild(renderer.domElement);

        // Events
        document.addEventListener('mousemove', onMouseMove, false);
        window.addEventListener('resize', onResize, false);

        animate();
    }

    function onMouseMove(event) {
        mouseX = event.clientX - windowHalfX;
        mouseY = event.clientY - windowHalfY;
    }

    function onResize() {
        windowHalfX = window.innerWidth / 2;
        windowHalfY = window.innerHeight / 2;
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    }

    function animate() {
        requestAnimationFrame(animate);

        var time = Date.now() * 0.00005;

        camera.position.x += (mouseX * 0.5 - camera.position.x) * 0.02;
        camera.position.y += (-mouseY * 0.5 - camera.position.y) * 0.02;
        camera.lookAt(scene.position);

        particles.rotation.x = time * 0.5;
        particles.rotation.y = time * 0.3;

        renderer.render(scene, camera);
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
