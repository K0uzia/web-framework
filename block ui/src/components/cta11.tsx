import { ArrowRight, Sparkles } from "lucide-react";
import type { ReactNode } from "react";

import { Button } from "@/components/ui/button";

import { cn } from "@/lib/utils";

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

interface CtaSideImageProps {
  heading: string;
  description: string;
  image: Image;
  buttons?: Buttons;
  className?: string;
}

interface Cta11Props extends CtaSideImageProps {
  icon?: ReactNode;
}
type Props = Partial<Cta11Props>;

const defaultProps: Cta11Props = {
  heading: "Call to Action",
  description: "Get access to our collection of pre-built blocks and components today.",
  image: {
    src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/images/1-16x9.jpg",
    alt: "Call to Action",
  },
  buttons: {
    primary: {
      text: "Get Access",
      url: "https://shadcnblocks.com",
    },
    secondary: {
      text: "Schedule a Demo",
      url: "https://shadcnblocks.com",
    },
  },
  icon: <Sparkles className="size-4" strokeWidth={1.5} />,
};

const Cta11 = (props: Props) => {
  const { heading, description, image, buttons, icon, className } = {
    ...defaultProps,
    ...props,
  };

  return (
    <section className={cn("py-32", className)}>
      <div className="container">
        <div className="mx-auto flex max-w-5xl flex-col overflow-hidden rounded-md border py-0 md:flex-row md:items-center">
          <img
            src={image.src}
            alt={image.alt}
            className="aspect-video object-cover md:aspect-auto md:max-w-md md:self-stretch"
          />
          <div className="p-6 md:max-w-96 md:py-8">
            <div className="mb-2 flex items-center gap-2">
              <span className="flex size-7 items-center justify-center rounded-md bg-muted">
                {icon}
              </span>
              <h3 className="text-3xl font-semibold">{heading}</h3>
            </div>
            <p className="text-muted-foreground">{description}</p>
            {buttons?.primary && (
              <Button className="mt-8" render={<a href={buttons.primary.url} />} nativeButton={false}>{buttons.primary.text}<ArrowRight className="size-4" /></Button>
            )}
          </div>
        </div>
      </div>
    </section>
  );
};

export { Cta11 };
