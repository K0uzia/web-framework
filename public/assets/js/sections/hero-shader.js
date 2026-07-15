/**
 * Fond shader hero78 (portage WebGL de shader3 shadcnblocks).
 */
(function () {
    var VERTEX_SHADER = '\nattribute vec2 a_position;\nvarying vec2 vUv;\nvoid main() {\n  vUv = a_position * 0.5 + 0.5;\n  gl_Position = vec4(a_position, 0.0, 1.0);\n}\n';
    var FRAGMENT_SHADER = '\nprecision highp float;\nconst float CIRCLE_SCALE = 1.0;\nvarying vec2 vUv;\nuniform float u_time;\nuniform vec3 u_resolution;\nuniform vec4 u_mouse;\nuniform vec3 u_color;\n#define R u_resolution\n#define PI 3.141528\nconst float refractIndex = 0.3;\nvec3 hsv(float h,float s,float v){return ((clamp(abs(fract(h+vec3(0.,.666,.333))*6.-3.)-1.,0.,1.)-1.)*s+1.)*v;}\nvec3 rgb2hsv(vec3 c){\n  vec4 K = vec4(0.0, -1.0/3.0, 2.0/3.0, -1.0);\n  vec4 p = mix(vec4(c.bg, K.wz), vec4(c.gb, K.xy), step(c.b, c.g));\n  vec4 q = mix(vec4(p.xyw, c.r), vec4(c.r, p.yzx), step(p.x, c.r));\n  float d = q.x - min(q.w, q.y);\n  float e = 1.0e-10;\n  return vec3(abs(q.z + (q.w - q.y) / (6.0*d + e)), d / (q.x + e), q.x);\n}\nfloat map(float val, float inA, float inB, float outA, float outB) {\n  return (val - inA) / (inB - inA) * (outB - outA) + outA;\n}\nfloat fresnel(vec3 direction, vec3 normal, float power, bool invert) {\n    vec3 halfDirection = normalize( normal + direction );\n    float cosine = dot( halfDirection, direction );\n    float product = max( cosine, 0.0 );\n    float factor = invert ? 1.0 - pow( product, power ) : pow( product, power );\n    return factor;\n}\nfloat lambert(vec3 normal, vec3 lightPos){\n  return max(dot(normal, lightPos), 0.05);\n}\nfloat hash12(vec2 p)\n{\n  vec3 p3  = fract(vec3(p.xyx) * .1031);\n    p3 += dot(p3, p3.yzx + 33.33);\n    return fract((p3.x + p3.y) * p3.z);\n}\nvec2 globalPos(vec2 pos){\n    vec2 mover = u_mouse.xy * 0.25;\n    return pos-mover + hash12(gl_FragCoord.xy + u_time * 0.1)*.0125;\n}\nfloat circle(vec2 pos,float lo, float hi){\n    return smoothstep(lo,hi,length(globalPos(pos)));\n}\nvec3 gradient(float d){\n    float a = smoothstep(0.0, 1.0, d);\n    vec3 colA = mix(vec3(0.05,0.05,0.05),vec3(.4,.6,.6),smoothstep(0.05,0.05,0.05) * d);\n    vec3 colB = mix(u_color * 0.06, u_color * 0.9, a);\n    return mix(colA, colB, d);\n}\nvec3 coloredRim(vec2 pos, float lamVal) {\n    float rimRadius = 0.035 * CIRCLE_SCALE + lamVal * 0.05 * CIRCLE_SCALE;\n    float innerRadius = 0.03 * CIRCLE_SCALE + lamVal * 0.025 * CIRCLE_SCALE;\n    float dist = length(globalPos(pos));\n    float rimMask = smoothstep(innerRadius, rimRadius, dist) * (1.0 - smoothstep(rimRadius, rimRadius + 0.05, dist));\n    vec3 hsvColor = rgb2hsv(u_color);\n    float baseHue = hsvColor.x;\n    float sat = hsvColor.y;\n    float hue = baseHue + dist * 2.0;\n    vec3 rimColor = hsv(hue, sat, 1.0);\n    return rimColor * rimMask;\n}\nfloat sdfCircle(vec2 pos, float r) {\n    return length(globalPos(pos)) - r;\n}\nvec2 sdfNormal(vec2 pos, float r) {\n    vec2 e = vec2(1.0/R.x, 1.0/R.y) * 1.5;\n    float dx = sdfCircle(pos + vec2(e.x, 0.0), r) - sdfCircle(pos - vec2(e.x, 0.0), r);\n    float dy = sdfCircle(pos + vec2(0.0, e.y), r) - sdfCircle(pos - vec2(0.0, e.y), r);\n    vec2 n = normalize(vec2(dx, dy));\n    return n;\n}\nvec3 shadeAt(vec2 pos, float lamVal) {\n    float dLocal = 1. - circle(pos, 0.05 * CIRCLE_SCALE, .3 * CIRCLE_SCALE + lamVal * 0.025 * CIRCLE_SCALE);\n    return gradient(dLocal);\n}\nvec3 refractSDFColor(vec2 pos, float lamVal) {\nfloat shiftPxR = 2.0;\nfloat shiftPxG = 0.0;\nfloat shiftPxB = -2.0;\nfloat r = 0.175 * CIRCLE_SCALE + lamVal * 0.6125 * CIRCLE_SCALE;\nvec2 n2 = normalize(sdfNormal(pos, r));\nvec2 px = 1.0 / R.xy;\nvec3 cR = shadeAt(pos + n2 * px * shiftPxR, lamVal);\nvec3 cG = shadeAt(pos + n2 * px * shiftPxG, lamVal);\nvec3 cB = shadeAt(pos + n2 * px * shiftPxB, lamVal);\nreturn vec3(cR.r, cG.g, cB.b);\n}\nvoid mainImage( out vec4 fragColor, in vec2 fragCoord )\n{\n    vec2 uv = fragCoord / R.xy;\n    vec2 p = (fragCoord/R.xy-.5)*vec2(R.x/R.y,1.)*2.;\n    float angle = PI / 4.0;\n    float cosA = cos(angle);\n    float sinA = sin(angle);\n    vec2 rotatedP = vec2(\n        p.x * cosA - p.y * sinA,\n        p.x * sinA + p.y * cosA\n    );\n    vec3 rd = vec3(0.0, 0.0, -1.0);\n    float rod_x = fract(rotatedP.x * 2.) * 2.0 - 1.0;\n    float rod_z = sqrt(1.0 - rod_x*rod_x);\n    vec3 n = vec3(rod_x, 0.0, -rod_z);\n    vec3 refracted_ray = mix(n, rd, refractIndex);\n    float dist = 0.8;\n    float z_dist = dist / (refracted_ray.z  - 0.8);\n    vec3 pos = vec3(p, 0.0) + z_dist*refracted_ray;\n    vec2 subPos = vec2(pos.xy * pos.z);\n    vec3 lpos = normalize(vec3(0., 0., -1.0));\n    float lambertVal = pow(lambert(n,lpos),2.);\n    float d = 1.-circle(subPos,0.05 * CIRCLE_SCALE,.3 * CIRCLE_SCALE + lambertVal * 0.0125 *CIRCLE_SCALE);\n    vec3 color = gradient(d);\n    vec3 rimColor = coloredRim(subPos, lambertVal);\n    color += rimColor * 0.65;\n    float g = 1.0 - abs(n.z);\n    g = g * 0.8 / (g * 0.8 - g + 1.0);\n    float glass = 1.-((1.0 - 0.3 * g) - g * hash12(gl_FragCoord.xy + d));\n    vec3 refrCol = refractSDFColor(subPos, lambertVal);\n    color = mix(color, refrCol, 0.65);\n    color += lambertVal * color * 0.05 * d;\n    color += color * glass * d * 0.05;\n    color = clamp(color,0.,1.);\n    color = pow(color,vec3(1./1.222));\n    fragColor = vec4(vec3(color),1.0);\n}\nvoid main() {\n  vec4 fragColor;\n  vec2 fragCoord = vUv * u_resolution.xy;\n  mainImage(fragColor, fragCoord);\n  gl_FragColor = fragColor;\n}\n';

    function hexToRgb(hex) {
        var value = (hex || '#bbffcc').replace('#', '');
        if (value.length !== 6) {
            return [0.733, 1.0, 0.8];
        }
        return [
            parseInt(value.slice(0, 2), 16) / 255,
            parseInt(value.slice(2, 4), 16) / 255,
            parseInt(value.slice(4, 6), 16) / 255,
        ];
    }

    function compileShader(gl, type, source) {
        var shader = gl.createShader(type);
        gl.shaderSource(shader, source);
        gl.compileShader(shader);
        if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
            console.error(gl.getShaderInfoLog(shader));
            gl.deleteShader(shader);
            return null;
        }
        return shader;
    }

    function createProgram(gl) {
        var vs = compileShader(gl, gl.VERTEX_SHADER, VERTEX_SHADER);
        var fs = compileShader(gl, gl.FRAGMENT_SHADER, FRAGMENT_SHADER);
        if (!vs || !fs) {
            return null;
        }
        var program = gl.createProgram();
        gl.attachShader(program, vs);
        gl.attachShader(program, fs);
        gl.linkProgram(program);
        if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
            console.error(gl.getProgramInfoLog(program));
            return null;
        }
        return program;
    }

    function initBackdrop(backdrop) {
        if (backdrop.dataset.heroShaderInit === '1') {
            return;
        }
        backdrop.dataset.heroShaderInit = '1';
        var canvas = backdrop.querySelector('.section-hero__shader-canvas');
        if (!canvas) {
            return;
        }
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }
        var gl = canvas.getContext('webgl', { alpha: false, antialias: false });
        if (!gl) {
            return;
        }
        var program = createProgram(gl);
        if (!program) {
            return;
        }
        var positionLoc = gl.getAttribLocation(program, 'a_position');
        var timeLoc = gl.getUniformLocation(program, 'u_time');
        var resolutionLoc = gl.getUniformLocation(program, 'u_resolution');
        var mouseLoc = gl.getUniformLocation(program, 'u_mouse');
        var colorLoc = gl.getUniformLocation(program, 'u_color');
        var buffer = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([
            -1, -1, 1, -1, -1, 1,
            -1, 1, 1, -1, 1, 1,
        ]), gl.STATIC_DRAW);
        var mouse = { x: 0, y: 0, targetX: 0, targetY: 0 };
        function onMove(event) {
            mouse.targetX = (event.clientX / window.innerWidth) * 2 - 1;
            mouse.targetY = -(event.clientY / window.innerHeight) * 2 + 1;
        }
        window.addEventListener('mousemove', onMove);
        var rgb = hexToRgb(backdrop.getAttribute('data-shader-color') || '#bbffcc');
        var start = performance.now();
        var rafId = 0;
        function resize() {
            var rect = backdrop.getBoundingClientRect();
            var width = Math.max(1, Math.floor(rect.width));
            var height = Math.max(1, Math.floor(rect.height));
            if (canvas.width !== width || canvas.height !== height) {
                canvas.width = width;
                canvas.height = height;
            }
            gl.viewport(0, 0, width, height);
        }
        function frame(now) {
            resize();
            mouse.x += (mouse.targetX - mouse.x) * 0.025;
            mouse.y += (mouse.targetY - mouse.y) * 0.025;
            gl.useProgram(program);
            gl.uniform1f(timeLoc, ((now - start) / 1000) * 0.5);
            gl.uniform3f(resolutionLoc, canvas.width, canvas.height, 1.0);
            gl.uniform4f(mouseLoc, mouse.x, mouse.y, 0.0, 0.0);
            gl.uniform3f(colorLoc, rgb[0], rgb[1], rgb[2]);
            gl.enableVertexAttribArray(positionLoc);
            gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
            gl.vertexAttribPointer(positionLoc, 2, gl.FLOAT, false, 0, 0);
            gl.drawArrays(gl.TRIANGLES, 0, 6);
            rafId = window.requestAnimationFrame(frame);
        }
        rafId = window.requestAnimationFrame(frame);
        if (typeof ResizeObserver !== 'undefined') {
            var observer = new ResizeObserver(resize);
            observer.observe(backdrop);
        }
        window.addEventListener('beforeunload', function () {
            window.cancelAnimationFrame(rafId);
            window.removeEventListener('mousemove', onMove);
        });
    }

    function boot() {
        document.querySelectorAll('[data-hero-shader]').forEach(initBackdrop);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
