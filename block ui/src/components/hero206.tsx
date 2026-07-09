"use client";

import {
  Marquee,
  MarqueeContent,
  MarqueeFade,
  MarqueeItem,
} from "@/components/kibo-ui/marquee";
import {
  ChevronLeft,
  ChevronRight,
  Copy,
  Plus,
  RotateCw,
  Share,
} from "lucide-react";

import { cn } from "@/lib/utils";

interface Image {
  src: string;
  alt: string;
  srcDark?: string;
}
interface Logo {
  src: string;
  alt: string;
  srcDark?: string;
  className?: string;
}

interface HeroSaasProps {
  className?: string;
  heading: string;
  description: string;
  logos?: Logo[];
  images?: Image[];
  mockupUrl?: string;
}

interface Hero206Props extends HeroSaasProps {}
type Props = Partial<Hero206Props>;

const defaultProps: Hero206Props = {
  heading: "The AI-powered CRM solution.",
  description: "Let AI help you manage accounts, deals, and handoffs in one place. Experience the future of CRM with AI-powered insights and automation.",
  logos: [
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-1.svg",
      alt: "Company logo 1",
      className: "h-7 w-auto",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-2.svg",
      alt: "Company logo 2",
      className: "h-7 w-auto",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-3.svg",
      alt: "Company logo 3",
      className: "h-7 w-auto",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-4.svg",
      alt: "Company logo 4",
      className: "h-7 w-auto",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-5.svg",
      alt: "Company logo 5",
      className: "h-5 w-auto",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-6.svg",
      alt: "Company logo 6",
      className: "h-7 w-auto",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-7.svg",
      alt: "Company logo 7",
      className: "h-7 w-auto",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-8.svg",
      alt: "Company logo 8",
      className: "h-7 w-auto",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-9.svg",
      alt: "Company logo 9",
      className: "h-7 w-auto",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-10.svg",
      alt: "Company logo 10",
      className: "h-7 w-auto",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-11.svg",
      alt: "Company logo 11",
      className: "h-7 w-auto",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-12.svg",
      alt: "Company logo 12",
      className: "h-7 w-auto",
    },
  ],
  images: [
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/modern/saas-hero/saas-hero-2-16x9.png",
      alt: "Product interface preview",
    },
  ],
  mockupUrl: "https://shadcnblocks.com/block/hero206",
};

/** Logo row under the hero copy. */
const MAX_LOGOS = 5;

const Hero206 = (props: Props) => {
  const { heading, description, logos, images, mockupUrl, className } = {
    ...defaultProps,
    ...props,
  };

  const logoRow = (logos ?? []).slice(0, MAX_LOGOS);
  const mockImage = images?.[0];

  return (
    <section
      className={cn("relative overflow-hidden bg-background", className)}
    >
      <div
        aria-hidden
        className="pointer-events-none absolute inset-0 z-0 bg-[linear-gradient(to_right,var(--color-border)_1px,transparent_1px),linear-gradient(to_bottom,var(--color-border)_1px,transparent_1px)] mask-[linear-gradient(to_bottom,transparent_0%,transparent_16%,black_30%,black_68%,transparent_100%)] bg-size-[4.5rem_4.5rem] opacity-50"
      />
      <div
        aria-hidden
        className="pointer-events-none absolute inset-x-0 top-0 z-0 h-48 bg-linear-to-b from-background from-35% to-transparent md:h-56"
      />
      <div className="relative z-10 container py-32">
        <header className="relative mx-auto max-w-4xl text-center">
          <h1 className="text-4xl font-semibold tracking-tight text-pretty text-foreground md:text-5xl lg:text-6xl">
            {heading}
          </h1>
          <p className="mx-auto mt-6 max-w-3xl text-balance text-muted-foreground lg:text-xl">
            {description}
          </p>
        </header>

        {logoRow.length > 0 && (
          <>
            <div className="relative mt-10 w-full md:hidden">
              <Marquee className="relative">
                <MarqueeContent>
                  {logoRow.map((logo, i) => (
                    <MarqueeItem key={`${logo.src}-${i}`}>
                      <img
                        src={logo.src}
                        alt={logo.alt}
                        className={cn(
                          "mx-4 h-5 w-auto max-w-20 object-contain opacity-70 dark:invert",
                          logo.className,
                        )}
                      />
                    </MarqueeItem>
                  ))}
                </MarqueeContent>
                <MarqueeFade
                  side="left"
                  className="pointer-events-none w-12 from-background"
                />
                <MarqueeFade
                  side="right"
                  className="pointer-events-none w-12 from-background"
                />
              </Marquee>
            </div>
            <div className="mx-auto mt-10 hidden w-fit max-w-full flex-wrap items-center justify-center gap-10 px-4 md:mt-12 md:flex md:gap-12">
              {logoRow.map((logo, i) => (
                <img
                  key={`${logo.src}-${i}`}
                  src={logo.src}
                  alt={logo.alt}
                  className={cn(
                    "h-7 w-auto max-w-[5.5rem] object-contain opacity-70 md:h-8 dark:invert",
                    logo.className,
                  )}
                />
              ))}
            </div>
          </>
        )}

        {mockImage && (
          <div className="relative mt-12 flex h-full w-full flex-col items-center justify-center">
            <BrowserMockup
              className="w-full shadow-[0_-20px_48px_-16px_rgba(0,0,0,0.1)]"
              url={mockupUrl ?? ""}
              image={mockImage}
            />
            <div className="absolute bottom-0 h-2/3 w-full bg-linear-to-t from-background to-transparent" />
          </div>
        )}
      </div>
    </section>
  );
};

export { Hero206 };

const BrowserMockup = ({
  className = "",
  url,
  image,
}: {
  className?: string;
  url: string;
  image: Image;
}) => (
  <div
    className={cn(
      "relative w-full overflow-hidden rounded-xl border md:rounded-2xl lg:rounded-3xl",
      className,
    )}
  >
    <div className="flex items-center justify-between gap-10 bg-muted px-8 py-4 lg:gap-25">
      <div className="flex items-center gap-2">
        <div className="size-3 rounded-full bg-red-500" />
        <div className="size-3 rounded-full bg-yellow-500" />
        <div className="size-3 rounded-full bg-green-500" />
        <div className="ml-6 hidden items-center gap-2 opacity-40 lg:flex">
          <ChevronLeft className="size-5" />
          <ChevronRight className="size-5" />
        </div>
      </div>
      <div className="flex w-full items-center justify-center">
        <p className="relative hidden w-full rounded-full bg-background px-4 py-1 text-center text-sm tracking-tight md:block">
          {url}
          <RotateCw className="absolute top-2 right-3 size-3.5" />
        </p>
      </div>

      <div className="flex items-center gap-4 opacity-40">
        <Share className="size-4" />
        <Plus className="size-4" />
        <Copy className="size-4" />
      </div>
    </div>

    <div className="relative w-full before:pointer-events-none before:absolute before:inset-x-0 before:top-0 before:z-10 before:h-16 before:bg-linear-to-b before:from-black/6 before:to-transparent md:before:h-20">
      {image.srcDark ? (
        <>
          <img
            src={image.src}
            alt={image.alt}
            className="hidden aspect-video h-full w-full object-cover object-top md:block dark:hidden"
          />
          <img
            src={image.srcDark}
            alt={image.alt}
            className="hidden aspect-video h-full w-full object-cover object-top md:hidden dark:md:block"
          />
          <img
            src={image.src}
            alt={image.alt}
            className="block h-full w-full object-cover md:hidden dark:hidden"
          />
          <img
            src={image.srcDark}
            alt={image.alt}
            className="hidden h-full w-full object-cover md:hidden dark:block"
          />
        </>
      ) : (
        <>
          <img
            src={image.src}
            alt={image.alt}
            className="hidden aspect-video h-full w-full object-cover object-top md:block"
          />
          <img
            src={image.src}
            alt={image.alt}
            className="block h-full w-full object-cover md:hidden"
          />
        </>
      )}
    </div>
    <div className="absolute bottom-0 z-10 flex w-full items-center justify-center bg-muted py-3 md:hidden">
      <p className="relative flex items-center gap-2 rounded-full px-8 py-1 text-center text-sm tracking-tight">
        {url}
      </p>
    </div>
  </div>
);
