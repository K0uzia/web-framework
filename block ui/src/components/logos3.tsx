"use client";

import Marquee from "react-fast-marquee";

import { cn } from "@/lib/utils";

type LogosSimpleStaticLogo = Logo & {
  href?: string;
};
interface Logo {
  src: string;
  alt: string;
  srcDark?: string;
  className?: string;
}

interface LogosSimpleStaticProps {
  heading: string;
  logos: LogosSimpleStaticLogo[];
  className?: string;
}

type Props = Partial<LogosSimpleStaticProps>;

const defaultProps: LogosSimpleStaticProps = {
  heading: "Trusted by these companies",
  logos: [
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-1.svg",
      alt: "Company logo 1",
      className: "h-7 w-auto",
      href: "https://www.shadcnblocks.com",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-2.svg",
      alt: "Company logo 2",
      className: "h-7 w-auto",
      href: "https://www.shadcnblocks.com",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-3.svg",
      alt: "Company logo 3",
      className: "h-7 w-auto",
      href: "https://www.shadcnblocks.com",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-4.svg",
      alt: "Company logo 4",
      className: "h-7 w-auto",
      href: "https://www.shadcnblocks.com",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-5.svg",
      alt: "Company logo 5",
      className: "h-5 w-auto",
      href: "https://www.shadcnblocks.com",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-6.svg",
      alt: "Company logo 6",
      className: "h-7 w-auto",
      href: "https://www.shadcnblocks.com",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-7.svg",
      alt: "Company logo 7",
      className: "h-7 w-auto",
      href: "https://www.shadcnblocks.com",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-8.svg",
      alt: "Company logo 8",
      className: "h-7 w-auto",
      href: "https://www.shadcnblocks.com",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-9.svg",
      alt: "Company logo 9",
      className: "h-7 w-auto",
      href: "https://www.shadcnblocks.com",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-10.svg",
      alt: "Company logo 10",
      className: "h-7 w-auto",
      href: "https://www.shadcnblocks.com",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-11.svg",
      alt: "Company logo 11",
      className: "h-7 w-auto",
      href: "https://www.shadcnblocks.com",
    },
    {
      src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/logos/fictional-company-logo-12.svg",
      alt: "Company logo 12",
      className: "h-7 w-auto",
      href: "https://www.shadcnblocks.com",
    },
  ],
};

const Logos3 = (props: Props) => {
  const { heading, logos, className } = {
    ...defaultProps,
    ...props,
  };

  return (
    <section className={cn("py-32", className)}>
      <div className="container flex flex-col items-center text-center">
        <h2 className="text-2xl font-semibold tracking-tight">{heading}</h2>
      </div>
      <div className="mt-8 lg:mt-12">
        <Marquee gradient gradientWidth={64} autoFill pauseOnHover speed={60}>
          {logos.map((logo, index) => (
            <div
              key={`${logo.src}-${index}`}
              className="mx-8 flex aspect-3/1 w-28 items-center justify-center sm:w-32 lg:mx-10"
            >
              <img
                src={logo.src}
                alt={logo.alt}
                className={cn(
                  logo.className,
                  "h-auto max-h-7 w-auto object-contain dark:invert",
                )}
              />
            </div>
          ))}
        </Marquee>
      </div>
    </section>
  );
};

export { Logos3 };
