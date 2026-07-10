import { Button } from "@/components/ui/button";

import { cn } from "@/lib/utils";

interface Button {
  text: string;
  url: string;
  icon?: React.ReactNode;
}
interface Buttons {
  primary?: Button;
  secondary?: Button;
}

interface CtaSimpleProps {
  heading: string;
  description: string;
  buttons?: Buttons;
  className?: string;
}

interface Cta38Props extends CtaSimpleProps {}
type Props = Partial<Cta38Props>;

const defaultProps: Cta38Props = {
  heading: "Call to Action",
  description:
    "Get access to our collection of pre-built blocks and components today.",
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
};

const Cta38 = (props: Props) => {
  const { heading, description, buttons, className } = {
    ...defaultProps,
    ...props,
  };

  return (
    <section className={cn("py-32", className)}>
      <div className="container mx-auto">
        <div className="mx-auto max-w-5xl rounded-xl bg-foreground p-8 text-background md:p-12 lg:p-16">
          <div className="flex flex-col items-center gap-4 text-center lg:gap-6">
            <h2 className="text-2xl font-semibold tracking-tight md:text-4xl">
              {heading}
            </h2>
            <p className="max-w-2xl text-background/70 lg:text-lg">
              {description}
            </p>
            <div className="mt-2 flex flex-col gap-3 sm:flex-row">
              {buttons?.primary && (
                <Button size="lg" variant="secondary" className="bg-background text-foreground hover:bg-background/90" render={<a href={buttons.primary.url} />} nativeButton={false}>{buttons.primary.text}</Button>
              )}
              {buttons?.secondary && (
                <Button size="lg" variant="outline" className="border-background/30 text-background hover:bg-background/10 hover:text-background" render={<a href={buttons.secondary.url} />} nativeButton={false}>{buttons.secondary.text}</Button>
              )}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export { Cta38 };
