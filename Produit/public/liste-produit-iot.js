import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls';

// Scene
const scene = new THREE.Scene();
scene.background = new THREE.Color(0x0a0f1c);

// Camera
const camera = new THREE.PerspectiveCamera(
  75,
  window.innerWidth / window.innerHeight,
  0.1,
  1000
);
camera.position.set(0, 3, 8);

// Renderer
const renderer = new THREE.WebGLRenderer({ antialias: true });
renderer.setSize(window.innerWidth, window.innerHeight);
document.body.appendChild(renderer.domElement);

// Controls
const controls = new OrbitControls(camera, renderer.domElement);

// Lights (lab style)
const ambientLight = new THREE.AmbientLight(0x404040, 1.5);
scene.add(ambientLight);

const neonLight = new THREE.PointLight(0x00ffff, 2, 50);
neonLight.position.set(0, 5, 5);
scene.add(neonLight);

// Floor (lab)
const floor = new THREE.Mesh(
  new THREE.PlaneGeometry(20, 20),
  new THREE.MeshStandardMaterial({ color: 0x111111 })
);
floor.rotation.x = -Math.PI / 2;
scene.add(floor);

// Function: create shelf
function createShelf(y) {
  const shelfGroup = new THREE.Group();

  const shelf = new THREE.Mesh(
    new THREE.BoxGeometry(10, 0.2, 1),
    new THREE.MeshStandardMaterial({ color: 0x222831 })
  );
  shelf.position.y = y;
  shelfGroup.add(shelf);

  // Add IoT products (boxes)
  for (let i = -4; i <= 4; i += 2) {
    const product = new THREE.Mesh(
      new THREE.BoxGeometry(0.8, 0.8, 0.8),
      new THREE.MeshStandardMaterial({
        color: 0x00adb5,
        emissive: 0x002f2f
      })
    );
    product.position.set(i, y + 0.6, 0);
    product.userData = { type: "iot-device" };

    shelfGroup.add(product);
  }

  return shelfGroup;
}

// Create multiple shelves
for (let i = 0; i < 4; i++) {
  const shelf = createShelf(i * 1.5);
  scene.add(shelf);
}

// Animation loop
function animate() {
  requestAnimationFrame(animate);

  scene.traverse((obj) => {
    if (obj.userData.type === "iot-device") {
      obj.rotation.y += 0.01;
    }
  });

  controls.update();
  renderer.render(scene, camera);
}

animate();

// Responsive
window.addEventListener('resize', () => {
  camera.aspect = window.innerWidth / window.innerHeight;
  camera.updateProjectionMatrix();
  renderer.setSize(window.innerWidth, window.innerHeight);
});
