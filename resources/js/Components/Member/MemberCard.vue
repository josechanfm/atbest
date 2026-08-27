<template>
  <div class="flip-card-container" @click="flipCardAnimation">
    <div class="flip-card" ref="flipCard">
      <!-- Front canvas -->
      <canvas ref="frontCanvas" class="flip-card-front"></canvas>
      <!-- Back canvas -->
      <canvas ref="backCanvas" class="flip-card-back"></canvas>
    </div>
  </div>
</template>

<script>
import { gsap } from "gsap";
import QRCode from "qrcode";
import axios from "axios";

export default {
  name: "MemberCard",
  props: {
    member: { type: Object, required: true },
    cardStyle: { type: Object, required: true },
    logoUrl: { type: String, default: "/storage/images/site_logo.png" },
  },
  data() {
    return {
      qrcodeData: "",
      intervalId: null,
      isFlipped: false,
      // Fixed canvas dimensions (pixels)
      canvasWidth: 400,
      canvasHeight: 250,
      titlePen: { color: "#555", font: "bold 20px sans-serif" },
      captionPen: { color: "rgba(0,0,0,0.7)", font: "13px sans-serif" }  };
  },
  computed: {
    // Precompute often used values
    orgName() {
      return this.member.organization?.["name_" + this.$t("lang")] || "";
    },
    fullName() {
      return (this.member.family_name || "") + (this.member.given_name || "");
    },
    avatarUrl() {
      return this.member.avatar || "/avatars/dummy-avatar.jpg";
    },
    bgImageUrl() {
      return this.cardStyle?.background
        ? "/storage/images/" + this.cardStyle.background
        : null;
    },
    fontStyle() {
      return this.cardStyle?.font_style || {};
    },
  },
  mounted() {
    this.drawFront();
    // Initially hide back canvas (GSAP will manage visibility)
    gsap.set(this.$refs.backCanvas, { rotationY: -180, opacity: 0 });
    gsap.set(this.$refs.frontCanvas, { opacity: 1 });
  },
  beforeUnmount() {
    if (this.intervalId) clearInterval(this.intervalId);
  },
  methods: {
    // ---- Drawing helpers ----
    loadImage(src) {
      return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = "Anonymous";
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = src;
      });
    },

    async drawFront() {
      const canvas = this.$refs.frontCanvas;
      const ctx = canvas.getContext("2d");
      const w = this.canvasWidth;
      const h = this.canvasHeight;
      canvas.width = w;
      canvas.height = h;

      // 1. Background image (if provided) with overlay
      let bgImg = null;
      if (this.bgImageUrl) {
        try {
          bgImg = await this.loadImage(this.bgImageUrl);
        } catch (e) {
          console.warn("Background image failed to load", e);
        }
      }
      if (bgImg) {
        ctx.drawImage(bgImg, 0, 0, w, h);
      } else {
        // Fallback gradient
        const grad = ctx.createLinearGradient(0, 0, w, 0);
        grad.addColorStop(0, "#1a1a2e");
        grad.addColorStop(1, "#16213e");
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, w, h);
      }

      // Overlay (darken)
      // ctx.fillStyle = "rgba(0,0,0,0.3)";
      // ctx.fillRect(0, 0, w, h);

      // 2. Text content
      const textColor = this.fontStyle?.color || this.titlePen.color;
      const fontFamily = this.fontStyle?.fontFamily || "sans-serif";
      ctx.textAlign = "left";
      ctx.textBaseline = "top";

      // Organization name (top center)
      ctx.textAlign = "center";
      ctx.font = `bold 16px ${fontFamily}`;
      ctx.fillStyle = textColor;
      ctx.fillText(this.orgName, w / 2, 16);

      // Left column: name, member number
      ctx.textAlign = "left";
      ctx.font = `12px ${fontFamily}`;
      ctx.fillStyle = this.captionPen.color;
      ctx.fillText("姓名", 20, 50);
      ctx.font = `bold 18px ${fontFamily}`;
      ctx.fillStyle = textColor;
      ctx.fillText(this.fullName, 20, 68);

      ctx.font = `12px ${fontFamily}`;
      ctx.fillStyle = this.captionPen.color;
      ctx.fillText("會員編號", 20, 100);
      ctx.font = `bold 14px monospace`;
      ctx.fillStyle = textColor;
      ctx.fillText(this.member.member_number || "", 20, 118);

      // Right: avatar
      let avatarImg = null;
      try {
        avatarImg = await this.loadImage(this.avatarUrl);
      } catch (e) {
        console.warn("Avatar failed to load", e);
      }
      const avatarSize = 64;
      const avatarX = w - avatarSize - 20;
      const avatarY = 44;
      if (avatarImg) {
        // Clip to circle
        ctx.save();
        ctx.beginPath();
        ctx.arc(avatarX + avatarSize / 2, avatarY + avatarSize / 2, avatarSize / 2, 0, Math.PI * 2);
        ctx.closePath();
        ctx.clip();
        ctx.drawImage(avatarImg, avatarX, avatarY, avatarSize, avatarSize);
        ctx.restore();
      } else {
        // Placeholder
        ctx.fillStyle = "#ccc";
        ctx.beginPath();
        ctx.arc(avatarX + avatarSize / 2, avatarY + avatarSize / 2, avatarSize / 2, 0, Math.PI * 2);
        ctx.fill();
        ctx.fillStyle = "#666";
        ctx.font = `24px sans-serif`;
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";
        ctx.fillText("👤", avatarX + avatarSize / 2, avatarY + avatarSize / 2);
      }

      // Border around avatar
      ctx.strokeStyle = "white";
      ctx.lineWidth = 2;
      ctx.beginPath();
      ctx.arc(avatarX + avatarSize / 2, avatarY + avatarSize / 2, avatarSize / 2, 0, Math.PI * 2);
      ctx.stroke();

      // Bottom: dates
      const dateY = h - 32;
      ctx.textAlign = "left";
      ctx.font = `11px ${fontFamily}`;
      ctx.fillStyle = this.captionPen.color;
      ctx.fillText("發出日期", 20, dateY);
      ctx.font = `bold 12px ${fontFamily}`;
      ctx.fillStyle = textColor;
      ctx.fillText(this.member.valid_at || "", 20, dateY + 18);

      ctx.textAlign = "left";
      ctx.font = `11px ${fontFamily}`;
      ctx.fillStyle = this.captionPen.color;
      ctx.fillText("有效日期", 130, dateY);
      ctx.font = `bold 12px ${fontFamily}`;
      ctx.fillStyle = textColor;
      ctx.fillText(this.member.expired_at || "", 130, dateY + 18);
    },

    async drawBack() {
      const canvas = this.$refs.backCanvas;
      const ctx = canvas.getContext("2d");
      const w = this.canvasWidth;
      const h = this.canvasHeight;
      canvas.width = w;
      canvas.height = h;

      // White background with subtle pattern or solid
      // ctx.fillStyle = "#f8f9fa";
      // ctx.fillRect(0, 0, w, h);

      // Border / card frame
      ctx.strokeStyle = "#e0e0e0";
      ctx.lineWidth = 1;
      ctx.strokeRect(5, 5, w - 10, h - 10);

      // Title
      ctx.textAlign = "center";
      ctx.textBaseline = "top";
      ctx.font = "bold 20px sans-serif";
      ctx.fillStyle = "#d35400";
      ctx.fillText("會員卡", w / 2, 12);

      // QR code
      let qrCanvas = document.createElement("canvas");
      if (this.qrcodeData) {
        try {
          await QRCode.toCanvas(qrCanvas, this.qrcodeData, {
            width: 120,
            margin: 2,
            color: {
              dark: "#26249a",
              light: "#ffffff",
            },
          });
        } catch (e) {
          console.warn("QR generation failed", e);
        }
      }
      // Draw QR on back canvas
      const qrSize = 120;
      const qrX = 30;
      const qrY = 50;
      if (qrCanvas.width > 0) {
        ctx.drawImage(qrCanvas, qrX, qrY, qrSize, qrSize);
      } else {
        // Placeholder
        ctx.fillStyle = "#ddd";
        ctx.fillRect(qrX, qrY, qrSize, qrSize);
        ctx.fillStyle = "#999";
        ctx.font = "14px sans-serif";
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";
        ctx.fillText("QR", qrX + qrSize / 2, qrY + qrSize / 2);
      }

      // Right side: benefits and contact
      const textX = qrX + qrSize + 20;
      ctx.textAlign = "left";
      ctx.textBaseline = "top";
      ctx.font = "13px sans-serif";
      ctx.fillStyle = "#333";
      const benefits = [
        "✓ 專屬會員活動優先報名",
        "✓ 使用二維碼累計積分",
      ];
      benefits.forEach((text, i) => {
        ctx.fillText(text, textX, 60 + i * 30);
      });

      ctx.font = "11px sans-serif";
      ctx.fillStyle = "#555";
      ctx.textAlign = "center";
      ctx.fillText("客服專線: +853 6381-6386", w / 2, h - 25);

      // Optionally, add a small logo from props.logoUrl
      if (this.logoUrl) {
        try {
          const logo = await this.loadImage(this.logoUrl);
          const logoSize = 30;
          ctx.drawImage(logo, w - logoSize - 15, h - logoSize - 15, logoSize, logoSize);
        } catch (e) {}
      }
    },

    // ---- QR code fetching ----
    getQrcode() {
      axios.get(route("member.getQrcode")).then((response) => {
        this.qrcodeData = response.data;
        this.drawBack();
      });
    },
    startQrRefresh() {
      this.getQrcode();
      this.intervalId = setInterval(() => {
        this.getQrcode();
      }, 5000);
    },

    // ---- Flip animation ----
    flipCardAnimation() {
      const duration = 0.6;
      const ease = "back.out(1)";
      const front = this.$refs.frontCanvas;
      const back = this.$refs.backCanvas;

      if (!this.isFlipped) {
        // Flip to back
        this.startQrRefresh();
        gsap.timeline()
          .to(this.$refs.flipCard, { rotationY: 180, duration, ease })
          .to(front, { opacity: 0, duration: duration / 2, ease: "power1.in" }, 0)
          .to(back, { opacity: 1, duration: duration / 2, ease: "power1.out" }, duration / 2);
      } else {
        // Flip to front
        if (this.intervalId) {
          clearInterval(this.intervalId);
          this.intervalId = null;
        }
        gsap.timeline()
          .to(this.$refs.flipCard, { rotationY: 0, duration, ease })
          .to(back, { opacity: 0, duration: duration / 2, ease: "power1.in" }, 0)
          .to(front, { opacity: 1, duration: duration / 2, ease: "power1.out" }, duration / 2);
      }
      this.isFlipped = !this.isFlipped;
    },
  },
};
</script>

<style scoped>
.flip-card-container {
  perspective: 1000px;
  height: 250px;
  cursor: pointer;
  width: 100%;
  max-width: 400px;  /* adjust to your design */
  margin: 0 auto;
}
.flip-card {
  position: relative;
  width: 100%;
  height: 100%;
  transform-style: preserve-3d;
}
.flip-card-front,
.flip-card-back {
  position: absolute;
  width: 100%;
  height: 100%;
  backface-visibility: hidden;
  border-radius: 12px;
  overflow: hidden;
  background-color: #bacdff;

}
.flip-card-front {
  /* GSAP will control opacity */
}
.flip-card-back {
  transform: rotateY(-180deg);
  opacity: 0;
}
canvas {
  display: block;
  width: 100%;
  height: 100%;
  border-radius: 12px;
}
</style>