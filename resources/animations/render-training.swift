import AppKit
import AVFoundation
import SceneKit
import simd

// Rebuild on macOS: swift resources/animations/render-training.swift public/videos
// The scene and skeletal motion are original procedural artwork, with no external assets.
let output = CommandLine.arguments.dropFirst().first ?? "public/videos"
try FileManager.default.createDirectory(atPath: output, withIntermediateDirectories: true)
let scene = SCNScene()
func color(_ hex: Int) -> NSColor {
    NSColor(red: CGFloat((hex >> 16) & 255) / 255, green: CGFloat((hex >> 8) & 255) / 255, blue: CGFloat(hex & 255) / 255, alpha: 1)
}
func material(_ hex: Int, metal: CGFloat = 0) -> SCNMaterial {
    let result = SCNMaterial()
    result.diffuse.contents = color(hex)
    result.lightingModel = .physicallyBased
    result.roughness.contents = 0.65
    result.metalness.contents = metal
    return result
}
let skin = material(0xb9815d), shirt = material(0x7ee2b8), shorts = material(0x243942)
let hair = material(0x24211e), shoe = material(0xf0f1e8), rubber = material(0x1c292b)
let steel = material(0x718b90, metal: 0.8)
func node(_ geometry: SCNGeometry, _ surface: SCNMaterial, _ parent: SCNNode = scene.rootNode) -> SCNNode {
    geometry.materials = [surface]
    let result = SCNNode(geometry: geometry)
    parent.addChildNode(result)
    return result
}
func ball(_ size: SIMD3<Float>, _ surface: SCNMaterial, _ parent: SCNNode) -> SCNNode {
    let result = node(SCNSphere(radius: 1), surface, parent)
    result.simdScale = size
    return result
}
func box(_ size: SIMD3<Float>, _ position: SIMD3<Float>, _ surface: SCNMaterial, radius: CGFloat = 0.03, parent: SCNNode = scene.rootNode) -> SCNNode {
    let result = node(SCNBox(width: CGFloat(size.x), height: CGFloat(size.y), length: CGFloat(size.z), chamferRadius: radius), surface, parent)
    result.simdPosition = position
    return result
}
func segment(_ a: SIMD3<Float>, _ b: SIMD3<Float>, _ object: SCNNode, radius: Float) {
    let direction = b - a
    object.simdPosition = (a + b) / 2
    object.simdOrientation = simd_quatf(from: SIMD3<Float>(0, 1, 0), to: simd_normalize(direction))
    object.simdScale = SIMD3(radius, simd_length(direction) / 2 + radius * 0.35, radius)
}
scene.background.contents = color(0x112724)
let floor = node(SCNFloor(), material(0x1d3531))
(floor.geometry as? SCNFloor)?.reflectivity = 0
_ = box(SIMD3(2.3, 0.025, 2.6), SIMD3(0, 0.015, 0), material(0x2b4944), radius: 0.012)
for i in -5...5 {
    _ = box(SIMD3(0.008, 0.002, 10), SIMD3(Float(i), 0.002, 0), material(0x2d4540), radius: 0)
    _ = box(SIMD3(10, 0.002, 0.008), SIMD3(0, 0.002, Float(i)), material(0x2d4540), radius: 0)
}
_ = box(SIMD3(1.2, 0.12, 0.4), SIMD3(-1.7, 0.52, -1.3), rubber)
for x: Float in [-2.1, -1.3] { _ = box(SIMD3(0.08, 0.5, 0.38), SIMD3(x, 0.25, -1.3), steel) }
for x: Float in [1.35, 2.15] { _ = box(SIMD3(0.06, 1.35, 0.07), SIMD3(x, 0.675, -1.6), steel) }
_ = box(SIMD3(1.15, 0.035, 0.035), SIMD3(1.75, 1.1, -1.6), steel)
for x: Float in [1.28, 1.4, 2.1, 2.22] {
    let plate = node(SCNCylinder(radius: 0.19, height: 0.06), rubber)
    plate.simdPosition = SIMD3(x, 1.1, -1.6)
    plate.eulerAngles.z = .pi / 2
}
for x: Float in [-2.6, 2.6] {
    let strip = material(0x7ee2b8)
    strip.emission.contents = color(0x4a8d74)
    _ = box(SIMD3(0.025, 2.7, 0.025), SIMD3(x, 1.35, -2.5), strip, radius: 0)
}
func light(_ type: SCNLight.LightType, _ intensity: CGFloat, _ hex: Int, _ position: SIMD3<Float>) {
    let lamp = SCNNode()
    lamp.light = SCNLight()
    lamp.light!.type = type
    lamp.light!.intensity = intensity
    lamp.light!.color = color(hex)
    lamp.simdPosition = position
    lamp.look(at: SCNVector3(0, 1, 0))
    if type == .spot {
        lamp.light!.spotInnerAngle = 35
        lamp.light!.spotOuterAngle = 85
        lamp.light!.castsShadow = true
        lamp.light!.shadowRadius = 5
        lamp.light!.shadowMapSize = CGSize(width: 2048, height: 2048)
        lamp.light!.shadowColor = NSColor.black.withAlphaComponent(0.5)
    }
    scene.rootNode.addChildNode(lamp)
}
light(.ambient, 160, 0xdceee8, SIMD3(0, 3, 0))
light(.spot, 550, 0xffe6cd, SIMD3(-3, 5, 4))
light(.omni, 120, 0x98c8ff, SIMD3(3, 2, 1))
light(.omni, 160, 0x7ee2b8, SIMD3(0, 3, -3))
let camera = SCNNode()
camera.camera = SCNCamera()
camera.camera!.fieldOfView = 32
camera.camera!.wantsHDR = true
camera.camera!.exposureOffset = 0.1
camera.simdPosition = SIMD3(3.2, 2.4, 5.3)
camera.look(at: SCNVector3(0, 1.0, 0))
scene.rootNode.addChildNode(camera)

let athlete = SCNNode()
scene.rootNode.addChildNode(athlete)
let pelvis = ball(SIMD3(0.22, 0.16, 0.14), shorts, athlete)
let waist = ball(SIMD3(0.20, 0.21, 0.125), shirt, athlete)
let chest = ball(SIMD3(0.285, 0.26, 0.15), shirt, athlete)
let head = SCNNode()
athlete.addChildNode(head)
_ = ball(SIMD3(0.125, 0.162, 0.12), skin, head)
let haircut = ball(SIMD3(0.13, 0.07, 0.122), hair, head)
haircut.simdPosition = SIMD3(0, 0.115, -0.012)
let nose = ball(SIMD3(0.025, 0.035, 0.03), skin, head)
nose.simdPosition = SIMD3(0, -0.01, 0.115)
for side: Float in [-1, 1] {
    let ear = ball(SIMD3(0.025, 0.04, 0.025), skin, head)
    ear.simdPosition = SIMD3(side * 0.12, 0, 0)
    let eye = ball(SIMD3(0.012, 0.009, 0.008), hair, head)
    eye.simdPosition = SIMD3(side * 0.046, 0.035, 0.109)
    let brow = box(SIMD3(0.035, 0.008, 0.009), SIMD3(side * 0.046, 0.057, 0.108), hair, radius: 0.003, parent: head)
    brow.eulerAngles.z = CGFloat(side * -0.1)
}
let mouth = box(SIMD3(0.045, 0.008, 0.01), SIMD3(0, -0.068, 0.103), material(0x6c4434), radius: 0.003, parent: head)
let neck = ball(SIMD3(repeating: 1), skin, athlete)
let logo = box(SIMD3(0.06, 0.025, 0.008), SIMD3(0, 0, 0), rubber, radius: 0.004, parent: athlete)
struct Limb {
    let upper: SCNNode, lower: SCNNode, joint: SCNNode, end: SCNNode
}
var arms: [Limb] = [], legs: [Limb] = [], weights: [SCNNode] = []
for _ in 0..<2 {
    arms.append(Limb(upper: ball(SIMD3(repeating: 1), skin, athlete), lower: ball(SIMD3(repeating: 1), skin, athlete), joint: ball(SIMD3(repeating: 0.067), skin, athlete), end: ball(SIMD3(0.055, 0.075, 0.045), skin, athlete)))
    legs.append(Limb(upper: ball(SIMD3(repeating: 1), shorts, athlete), lower: ball(SIMD3(repeating: 1), skin, athlete), joint: ball(SIMD3(repeating: 0.085), skin, athlete), end: ball(SIMD3(0.09, 0.065, 0.17), shoe, athlete)))
    let weight = SCNNode()
    athlete.addChildNode(weight)
    _ = box(SIMD3(0.27, 0.035, 0.035), SIMD3(0, 0, 0), steel, radius: 0.012, parent: weight)
    for side: Float in [-1, 1] {
        _ = box(SIMD3(0.09, 0.14, 0.14), SIMD3(side * 0.12, 0, 0), rubber, parent: weight)
    }
    weights.append(weight)
}
struct Pose {
    var hip: SIMD3<Float> = SIMD3(0, 0.99, 0)
    var lean: Float = 0
    var knees: [SIMD3<Float>] = [SIMD3(-0.16, 0.56, 0), SIMD3(0.16, 0.56, 0)]
    var feet: [SIMD3<Float>] = [SIMD3(-0.16, 0.10, 0), SIMD3(0.16, 0.10, 0)]
    var elbows: [SIMD3<Float>] = [SIMD3(-0.34, 1.22, 0), SIMD3(0.34, 1.22, 0)]
    var hands: [SIMD3<Float>] = [SIMD3(-0.34, 0.94, 0.03), SIMD3(0.34, 0.94, 0.03)]
    var weight: Float = 0
}
func routine(_ part: Int, _ progress: Float) -> Pose {
    var pose = Pose()
    let wave = sin(progress * .pi * 2)
    let rep = (1 - cos(progress * .pi * 2)) / 2
    if part == 0 {
        pose.hip.y += 0.025 * sin(progress * .pi * 4)
        for i in 0..<2 {
            let side: Float = i == 0 ? -1 : 1
            let stride = wave * side
            pose.knees[i].z = stride * 0.16 + 0.04
            pose.feet[i].z = stride * 0.32
            pose.feet[i].y += max(0, stride) * 0.10
            pose.elbows[i].z = -stride * 0.13
            pose.hands[i].z = -stride * 0.23 + 0.06
        }
    } else if part == 1 {
        pose.hip = SIMD3(0, 0.99 - 0.36 * rep, -0.20 * rep)
        pose.lean = 0.35 * rep
        for i in 0..<2 {
            let side: Float = i == 0 ? -1 : 1
            pose.feet[i].x = side * 0.25
            pose.knees[i] = SIMD3(side * 0.26, 0.55 - rep * 0.09, rep * 0.28)
            pose.elbows[i] = SIMD3(side * 0.32, 1.22 - 0.20 * rep, 0.20 * rep)
            pose.hands[i] = SIMD3(side * 0.20, 0.94 + 0.25 * rep, 0.42 * rep)
        }
    } else if part == 2 {
        pose.hip.y -= 0.30 * rep
        pose.knees[0] = SIMD3(-0.17, 0.56 - 0.13 * rep, 0.42 * rep)
        pose.feet[0].z = 0.46 * rep
        pose.knees[1] = SIMD3(0.17, 0.56 - 0.35 * rep, -0.24 * rep)
        pose.feet[1].z = -0.56 * rep
        for i in 0..<2 {
            pose.elbows[i].y -= 0.3 * rep
            pose.hands[i].y -= 0.3 * rep
        }
    } else if part == 3 {
        pose.weight = 1
        for i in 0..<2 {
            let angle = rep * 2.35
            pose.hands[i].y = 1.22 - 0.28 * cos(angle)
            pose.hands[i].z = 0.28 * sin(angle)
        }
    } else if part == 4 {
        pose.weight = 1
        for i in 0..<2 {
            let side: Float = i == 0 ? -1 : 1
            pose.elbows[i] = SIMD3(side * (0.46 - 0.10 * rep), 1.50 + 0.25 * rep, 0)
            pose.hands[i] = SIMD3(side * (0.46 - 0.18 * rep), 1.78 + 0.25 * rep, 0.015)
        }
    } else {
        pose.hip = SIMD3(0, 0.57 - 0.17 * rep, -0.12)
        pose.lean = 1.32
        pose.knees = [SIMD3(-0.16, 0.34 - 0.08 * rep, -0.51), SIMD3(0.16, 0.34 - 0.08 * rep, -0.51)]
        pose.feet = [SIMD3(-0.16, 0.09, -0.94), SIMD3(0.16, 0.09, -0.94)]
        pose.elbows = [SIMD3(-0.34 - 0.12 * rep, 0.31 - 0.07 * rep, 0.34 - 0.10 * rep), SIMD3(0.34 + 0.12 * rep, 0.31 - 0.07 * rep, 0.34 - 0.10 * rep)]
        pose.hands = [SIMD3(-0.34, 0.10, 0.49), SIMD3(0.34, 0.10, 0.49)]
    }
    return pose
}
func blend(_ a: Pose, _ b: Pose, _ f: Float) -> Pose {
    var p = Pose()
    p.hip = a.hip + (b.hip - a.hip) * f
    p.lean = a.lean + (b.lean - a.lean) * f
    p.weight = a.weight + (b.weight - a.weight) * f
    for i in 0..<2 {
        p.knees[i] = a.knees[i] + (b.knees[i] - a.knees[i]) * f
        p.feet[i] = a.feet[i] + (b.feet[i] - a.feet[i]) * f
        p.elbows[i] = a.elbows[i] + (b.elbows[i] - a.elbows[i]) * f
        p.hands[i] = a.hands[i] + (b.hands[i] - a.hands[i]) * f
    }
    return p
}
func animate(_ time: Float) {
    let part = min(5, Int(time / 3))
    let local = time - Float(part) * 3
    var pose: Pose
    if local < 0.55 {
        let f = local / 0.55
        pose = blend(routine((part + 5) % 6, 1), routine(part, 0), f * f * (3 - 2 * f))
    } else {
        pose = routine(part, (local - 0.55) / 2.45)
    }
    let rotation = simd_quatf(angle: pose.lean, axis: SIMD3(1, 0, 0))
    func torso(_ p: SIMD3<Float>) -> SIMD3<Float> { pose.hip + rotation.act(p) }
    pelvis.simdPosition = pose.hip
    waist.simdPosition = torso(SIMD3(0, 0.19, 0))
    chest.simdPosition = torso(SIMD3(0, 0.39, 0))
    waist.simdOrientation = rotation
    chest.simdOrientation = rotation
    head.simdPosition = torso(SIMD3(0, 0.77, 0))
    head.simdOrientation = simd_quatf(angle: pose.lean * 0.65, axis: SIMD3(1, 0, 0))
    segment(torso(SIMD3(0, 0.57, 0)), torso(SIMD3(0, 0.68, 0)), neck, radius: 0.065)
    logo.simdPosition = torso(SIMD3(-0.11, 0.47, 0.137))
    logo.simdOrientation = rotation
    for i in 0..<2 {
        let side: Float = i == 0 ? -1 : 1
        segment(torso(SIMD3(side * 0.265, 0.50, 0)), pose.elbows[i], arms[i].upper, radius: 0.083)
        segment(pose.elbows[i], pose.hands[i], arms[i].lower, radius: 0.062)
        arms[i].joint.simdPosition = pose.elbows[i]
        arms[i].end.simdPosition = pose.hands[i]
        segment(pose.hip + SIMD3(side * 0.14, -0.02, 0), pose.knees[i], legs[i].upper, radius: 0.115)
        segment(pose.knees[i], pose.feet[i], legs[i].lower, radius: 0.075)
        legs[i].joint.simdPosition = pose.knees[i]
        legs[i].end.simdPosition = pose.feet[i] + SIMD3(0, -0.03, 0.065)
        weights[i].simdPosition = pose.hands[i]
        weights[i].opacity = CGFloat(pose.weight)
    }
}

let width = 960, height = 960, fps: Int32 = 30, seconds = 18
let renderer = SCNRenderer(device: MTLCreateSystemDefaultDevice(), options: nil)
renderer.scene = scene
renderer.pointOfView = camera
let url = URL(fileURLWithPath: output).appendingPathComponent("gym-training.mp4")
try? FileManager.default.removeItem(at: url)
let writer = try AVAssetWriter(outputURL: url, fileType: .mp4)
writer.shouldOptimizeForNetworkUse = true
let input = AVAssetWriterInput(mediaType: .video, outputSettings: [AVVideoCodecKey: AVVideoCodecType.h264, AVVideoWidthKey: width, AVVideoHeightKey: height, AVVideoCompressionPropertiesKey: [AVVideoAverageBitRateKey: 2_000_000, AVVideoProfileLevelKey: AVVideoProfileLevelH264HighAutoLevel]])
let adaptor = AVAssetWriterInputPixelBufferAdaptor(assetWriterInput: input, sourcePixelBufferAttributes: [kCVPixelBufferPixelFormatTypeKey as String: kCVPixelFormatType_32ARGB, kCVPixelBufferWidthKey as String: width, kCVPixelBufferHeightKey as String: height, kCVPixelBufferCGImageCompatibilityKey as String: true, kCVPixelBufferCGBitmapContextCompatibilityKey as String: true])
writer.add(input)
guard writer.startWriting() else { fatalError("Cannot start video writer: \(String(describing: writer.error))") }
writer.startSession(atSourceTime: .zero)
for frame in 0..<(seconds * Int(fps)) {
    autoreleasepool {
        let time = Float(frame) / Float(fps)
        animate(time)
        let snapshot = renderer.snapshot(atTime: Double(time), with: CGSize(width: width, height: height), antialiasingMode: .multisampling4X)
        guard let image = snapshot.cgImage(forProposedRect: nil, context: nil, hints: nil) else { fatalError("Missing frame") }
        if frame == 45 || frame == 135 || frame == 315 || frame == 405 || frame == 495 {
            let bitmap = NSBitmapImageRep(cgImage: image)
            let destination = frame == 45
                ? URL(fileURLWithPath: output).appendingPathComponent("gym-training-poster.jpg")
                : FileManager.default.temporaryDirectory.appendingPathComponent("gym-training-preview-\(frame).jpg")
            try! bitmap.representation(using: .jpeg, properties: [.compressionFactor: 0.9])!.write(to: destination)
        }
        while !input.isReadyForMoreMediaData { Thread.sleep(forTimeInterval: 0.005) }
        var pixelBuffer: CVPixelBuffer?
        CVPixelBufferPoolCreatePixelBuffer(nil, adaptor.pixelBufferPool!, &pixelBuffer)
        guard let buffer = pixelBuffer else { fatalError("Cannot allocate frame") }
        CVPixelBufferLockBaseAddress(buffer, [])
        let context = CGContext(data: CVPixelBufferGetBaseAddress(buffer), width: width, height: height, bitsPerComponent: 8, bytesPerRow: CVPixelBufferGetBytesPerRow(buffer), space: CGColorSpaceCreateDeviceRGB(), bitmapInfo: CGImageAlphaInfo.noneSkipFirst.rawValue)!
        context.draw(image, in: CGRect(x: 0, y: 0, width: width, height: height))
        CVPixelBufferUnlockBaseAddress(buffer, [])
        guard adaptor.append(buffer, withPresentationTime: CMTime(value: Int64(frame), timescale: fps)) else { fatalError("Cannot encode frame: \(String(describing: writer.error))") }
        if frame % 90 == 0 { print("Rendered \(frame)/\(seconds * Int(fps)) frames"); fflush(stdout) }
    }
}
input.markAsFinished()
writer.endSession(atSourceTime: CMTime(value: Int64(seconds), timescale: 1))
let finished = DispatchSemaphore(value: 0)
writer.finishWriting { finished.signal() }
finished.wait()
guard writer.status == .completed else { fatalError("Video export failed: \(String(describing: writer.error))") }
print("Created \(url.path): \(seconds)s, \(fps)fps, \(width)×\(height)")
