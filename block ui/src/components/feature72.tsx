import {
  ArrowRight,
  Blocks,
  Globe,
  Layers,
  Palette,
  Rocket,
  Zap,
} from "lucide-react";

import { Button } from "@/components/ui/button";

import { cn } from "@/lib/utils";

interface FeatureCardListItem {
  title: string;
  description: string;
  image: Image;
  href?: string;
  icon?: React.ReactNode;
  label?: string;
}
interface Image {
  src: string;
  alt: string;
  srcDark?: string;
}
interface Button {
  text: string;
  url: string;
  icon?: React.ReactNode;
}
interface Buttons {
  primary?: Button;
  secondary?: Button;
}

interface FeatureCardListProps {
  heading: string;
  description?: string;
  features?: FeatureCardListItem[];
  buttons?: Buttons;
  className?: string;
}

interface Feature72Props extends FeatureCardListProps {}
type Props = Partial<Feature72Props>;

const defaultProps: Feature72Props = {
  heading: "Build faster with production ready features",
  description:
    "Every component is built with React, Tailwind CSS, and shadcn/ui. Copy, paste, and customize to match your brand in minutes.",
  features: [
    {
      icon: <Zap className="size-5" />,
      title: "Full Source Code",
      description:
        "Every block ships as plain React you own. No runtime dependency, no SDK lock-in, just copy and customize.",
      image: {
        src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/modern/saas-details/saas-card-detail-1-4x3.svg",
        alt: "Full Source Code",
      },
      href: "https://www.shadcnblocks.com",
    },
    {
      icon: <Palette className="size-5" />,
      title: "Responsive Design",
      description:
        "Every block adapts seamlessly from mobile to desktop with Tailwind's mobile-first utility classes.",
      image: {
        src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/modern/saas-details/saas-card-detail-2-4x3.svg",
        alt: "Responsive Design",
      },
      href: "https://www.shadcnblocks.com",
    },
    {
      icon: <Layers className="size-5" />,
      title: "Customizable",
      description:
        "Override any prop, swap icons, adjust spacing — every block is designed to be extended, not locked down.",
      image: {
        src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/modern/saas-details/saas-card-detail-3-4x3.svg",
        alt: "Customizable",
      },
      href: "https://www.shadcnblocks.com",
    },
    {
      icon: <Rocket className="size-5" />,
      title: "Production Ready",
      description:
        "Battle-tested in real projects. No placeholder hacks, no lorem ipsum — clean code you can ship today.",
      image: {
        src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/modern/saas-details/saas-card-detail-4-4x3.svg",
        alt: "Production Ready",
      },
      href: "https://www.shadcnblocks.com",
    },
    {
      icon: <Blocks className="size-5" />,
      title: "Registry Compatible",
      description:
        "Install blocks directly with the shadcn CLI. Dependencies and registry items are listed in every block's MDX.",
      image: {
        src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/modern/saas-details/saas-card-detail-5-4x3.svg",
        alt: "Registry Compatible",
      },
      href: "https://www.shadcnblocks.com",
    },
    {
      icon: <Globe className="size-5" />,
      title: "Framework Agnostic",
      description:
        "Plain ESM + React that works with Next.js, Vite, Remix, and Astro without any Shadcnblocks SDK.",
      image: {
        src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/modern/saas-details/saas-card-detail-6-4x3.svg",
        alt: "Framework Agnostic",
      },
      href: "https://www.shadcnblocks.com",
    },
  ],
  buttons: {
    primary: {
      text: "Browse Components",
      url: "https://www.shadcnblocks.com",
    },
  },
};

const Feature72 = (props: Props) => {
  const { heading, description, buttons, features, className } = {
    ...defaultProps,
    ...props,
  };

  return (
    <section className={cn("py-32", className)}>
      <div className="container mx-auto">
        <div className="mb-9 lg:mb-14 lg:max-w-sm">
          <h2 className="mb-3 text-3xl font-semibold tracking-tight text-balance md:mb-4 md:text-4xl lg:mb-6">
            {heading}
          </h2>
          {description && (
            <p className="mb-8 text-muted-foreground lg:text-lg">
              {description}
            </p>
          )}
          {buttons?.primary && (
            <Button variant="link" render={<a href={buttons.primary.url} className="group flex items-center font-medium md:text-base lg:text-lg" />} nativeButton={false}>{buttons.primary.text}<ArrowRight /></Button>
          )}
        </div>
        <div className="grid gap-6 md:grid-cols-2">
          {features?.slice(0, 4).map((feature, i) => (
            <div
              key={i}
              className="flex flex-col overflow-clip rounded-xl border border-border"
            >
              <a href={feature.href}>
                <img
                  src={feature.image.src}
                  alt={feature.image.alt}
                  className="aspect-4/3 h-full w-full object-cover object-top transition-opacity hover:opacity-80"
                />
              </a>
              <div className="px-6 pt-8 pb-8 md:px-8 md:pb-10 lg:px-10 lg:pb-12">
                <h3 className="mb-2 text-lg font-semibold md:text-2xl">
                  {feature.title}
                </h3>
                <p className="mb-4 text-muted-foreground lg:text-lg">
                  {feature.description}
                </p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

export { Feature72 };
