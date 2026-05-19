/**
 * TMV 3D Effects - Three.js Background Particles + Wireframe Globe
 * Advanced 3D visual effects for the Trademark Verification System
 * Features: 2500 particles, connection lines, secondary particle layer,
 * 3D wireframe globe, pulsing animations, enhanced mouse reactivity
 */
(function() {
    'use strict';

    // Only initialize on verify and apply pages
    var container = document.querySelector('.tmv-verify-container, .tmv-apply-container');
    if (!container) return;

    // Check if Three.js is available
    if (typeof THREE === 'undefined') return;

    var scene, camera, renderer, particles, particlesSecondary;
    var globe, globeEdges;
    var linesMesh, linesGeometry, linesPositions, linesColors;
    var mouseX = 0, mouseY = 0;
    var windowHalfX = window.innerWidth / 2;
    var windowHalfY = window.innerHeight / 2;

    var PARTICLE_COUNT = 2500;
    var SECONDARY_PARTICLE_COUNT = 500;
    var CONNECTION_DISTANCE = 120;
    var MAX_CONNECTIONS = 800;

    function init() {
        // Mobile/low-end device detection: reduce effects
        var isMobile = window.innerWidth < 768;
        var isLowEnd = navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4;
        if (isMobile || isLowEnd) {
            PARTICLE_COUNT = 800;
            SECONDARY_PARTICLE_COUNT = 200;
            MAX_CONNECTIONS = 300;
            CONNECTION_DISTANCE = 80;
        }

        // Create canvas container
        var canvasContainer = document.createElement('div');
        canvasContainer.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:0;opacity:0.6;';
        canvasContainer.id = 'tmv-3d-canvas';
        document.body.insertBefore(canvasContainer, document.body.firstChild);

        // Scene
        scene = new THREE.Scene();

        // Camera
        camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 1, 2000);
        camera.position.z = 500;

        // --- Primary Particles (2500) ---
        var geometry = new THREE.BufferGeometry();
        var vertices = [];
        var colors = [];

        var color1 = new THREE.Color(0x1a5c3a); // Green
        var color2 = new THREE.Color(0xffd700); // Gold
        var color3 = new THREE.Color(0x2d8a5e); // Light Green
        var color4 = new THREE.Color(0x00d4ff); // Cyan
        var color5 = new THREE.Color(0xff006e); // Magenta
        var color6 = new THREE.Color(0xffffff); // White

        for (var i = 0; i < PARTICLE_COUNT; i++) {
            var x = Math.random() * 2000 - 1000;
            var y = Math.random() * 2000 - 1000;
            var z = Math.random() * 2000 - 1000;
            vertices.push(x, y, z);

            var colorChoice = Math.random();
            var c;
            if (colorChoice < 0.25) c = color1;
            else if (colorChoice < 0.45) c = color2;
            else if (colorChoice < 0.60) c = color3;
            else if (colorChoice < 0.75) c = color4;
            else if (colorChoice < 0.88) c = color5;
            else c = color6;

            colors.push(c.r, c.g, c.b);
        }

        geometry.setAttribute('position', new THREE.Float32BufferAttribute(vertices, 3));
        geometry.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));

        var material = new THREE.PointsMaterial({
            size: 3,
            vertexColors: true,
            transparent: true,
            opacity: 0.7,
            blending: THREE.AdditiveBlending,
            sizeAttenuation: true
        });

        particles = new THREE.Points(geometry, material);
        scene.add(particles);

        // --- Secondary Particles (500, smaller, different speed) ---
        var geoSecondary = new THREE.BufferGeometry();
        var vertsSecondary = [];
        var colorsSecondary = [];

        for (var j = 0; j < SECONDARY_PARTICLE_COUNT; j++) {
            var sx = Math.random() * 1600 - 800;
            var sy = Math.random() * 1600 - 800;
            var sz = Math.random() * 1600 - 800;
            vertsSecondary.push(sx, sy, sz);

            var sc = Math.random() < 0.5 ? color4 : color6;
            colorsSecondary.push(sc.r, sc.g, sc.b);
        }

        geoSecondary.setAttribute('position', new THREE.Float32BufferAttribute(vertsSecondary, 3));
        geoSecondary.setAttribute('color', new THREE.Float32BufferAttribute(colorsSecondary, 3));

        var matSecondary = new THREE.PointsMaterial({
            size: 1.5,
            vertexColors: true,
            transparent: true,
            opacity: 0.5,
            blending: THREE.AdditiveBlending,
            sizeAttenuation: true
        });

        particlesSecondary = new THREE.Points(geoSecondary, matSecondary);
        scene.add(particlesSecondary);

        // --- Connection Lines (LineSegments) ---
        linesGeometry = new THREE.BufferGeometry();
        linesPositions = new Float32Array(MAX_CONNECTIONS * 6);
        linesColors = new Float32Array(MAX_CONNECTIONS * 6);
        linesGeometry.setAttribute('position', new THREE.BufferAttribute(linesPositions, 3));
        linesGeometry.setAttribute('color', new THREE.BufferAttribute(linesColors, 3));
        linesGeometry.setDrawRange(0, 0);

        var linesMaterial = new THREE.LineBasicMaterial({
            vertexColors: true,
            transparent: true,
            opacity: 0.35,
            blending: THREE.AdditiveBlending
        });

        linesMesh = new THREE.LineSegments(linesGeometry, linesMaterial);
        scene.add(linesMesh);

        // --- 3D Wireframe Globe (Icosahedron) --- skip on mobile/low-end
        if (!isMobile && !isLowEnd) {
            var globeGeo = new THREE.IcosahedronGeometry(180, 2);
            var globeMat = new THREE.MeshBasicMaterial({
                color: 0x1a5c3a,
                wireframe: true,
                transparent: true,
                opacity: 0.15
            });
            globe = new THREE.Mesh(globeGeo, globeMat);
            scene.add(globe);

            // Globe edges for subtle gold glow
            var edgesGeo = new THREE.EdgesGeometry(globeGeo);
            var edgesMat = new THREE.LineBasicMaterial({
                color: 0xffd700,
                transparent: true,
                opacity: 0.1
            });
            globeEdges = new THREE.LineSegments(edgesGeo, edgesMat);
            scene.add(globeEdges);
        }

        // Renderer
        renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
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

    function updateConnections() {
        var positions = particles.geometry.attributes.position.array;
        var lineIndex = 0;
        var lineColorGreen = new THREE.Color(0x1a5c3a);
        var lineColorGold = new THREE.Color(0xffd700);

        // Sample a subset for performance (check first 300 particles)
        var checkCount = Math.min(300, PARTICLE_COUNT);
        for (var i = 0; i < checkCount && lineIndex < MAX_CONNECTIONS; i++) {
            var ix = i * 3;
            var px1 = positions[ix];
            var py1 = positions[ix + 1];
            var pz1 = positions[ix + 2];

            for (var j = i + 1; j < checkCount && lineIndex < MAX_CONNECTIONS; j++) {
                var jx = j * 3;
                var px2 = positions[jx];
                var py2 = positions[jx + 1];
                var pz2 = positions[jx + 2];

                var dx = px1 - px2;
                var dy = py1 - py2;
                var dz = pz1 - pz2;
                var dist = Math.sqrt(dx * dx + dy * dy + dz * dz);

                if (dist < CONNECTION_DISTANCE) {
                    var alpha = 1.0 - (dist / CONNECTION_DISTANCE);
                    var idx = lineIndex * 6;

                    linesPositions[idx] = px1;
                    linesPositions[idx + 1] = py1;
                    linesPositions[idx + 2] = pz1;
                    linesPositions[idx + 3] = px2;
                    linesPositions[idx + 4] = py2;
                    linesPositions[idx + 5] = pz2;

                    // Alternate green and gold lines
                    var lc = (i + j) % 2 === 0 ? lineColorGreen : lineColorGold;
                    linesColors[idx] = lc.r * alpha;
                    linesColors[idx + 1] = lc.g * alpha;
                    linesColors[idx + 2] = lc.b * alpha;
                    linesColors[idx + 3] = lc.r * alpha;
                    linesColors[idx + 4] = lc.g * alpha;
                    linesColors[idx + 5] = lc.b * alpha;

                    lineIndex++;
                }
            }
        }

        linesGeometry.setDrawRange(0, lineIndex * 2);
        linesGeometry.attributes.position.needsUpdate = true;
        linesGeometry.attributes.color.needsUpdate = true;
    }

    function animate() {
        requestAnimationFrame(animate);

        var time = Date.now() * 0.00005;

        // Enhanced mouse influence (2x multiplier)
        camera.position.x += (mouseX * 1.0 - camera.position.x) * 0.02;
        camera.position.y += (-mouseY * 1.0 - camera.position.y) * 0.02;
        camera.lookAt(scene.position);

        // Primary particles rotation
        particles.rotation.x = time * 0.5;
        particles.rotation.y = time * 0.3;

        // Pulsing size animation
        var pulse = Math.sin(Date.now() * 0.002) * 0.5 + 3.0;
        particles.material.size = pulse;

        // Secondary particles (different speed and direction)
        particlesSecondary.rotation.x = time * 0.3;
        particlesSecondary.rotation.y = -time * 0.4;
        particlesSecondary.rotation.z = time * 0.2;

        // Globe rotation on all 3 axes
        if (globe) {
            globe.rotation.x += 0.001;
            globe.rotation.y += 0.0015;
            globe.rotation.z += 0.0005;
            globeEdges.rotation.x = globe.rotation.x;
            globeEdges.rotation.y = globe.rotation.y;
            globeEdges.rotation.z = globe.rotation.z;
        }

        // Update connection lines
        updateConnections();

        renderer.render(scene, camera);
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
